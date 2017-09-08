<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Language_Db_Access
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Db_Access implements Mlp_Data_Access {

	/**
	 * @var int
	 */
	private $page_size = 20;

	/**
	 * @var string
	 */
	private $table_name;

	/**
	 * @var array
	 */
	private $fields = array(
		'ID' => '%d',
		'english_name' => '%s',
		'native_name' => '%s',
		'custom_name' => '%s',
		'is_rtl' => '%d',
		'iso_639_1' => '%s',
		'iso_639_2' => '%s',
		'wp_locale' => '%s',
		'http_name' => '%s',
		'priority' => '%d',
	);

	/**
	 * @var array
	 */
	private $compare_operators = array(
		'=',
		'<=>',
		'>',
		'>=',
		'<',
		'<=',
		'LIKE',
		'!=',
		'<>',
		'NOT LIKE',
		'NOT REGEXP',
		'REGEXP',
		'RLIKE',
	);

	/**
	 * @param     $table_name
	 * @param int $page_size
	 */
	public function __construct( $table_name, $page_size = 20 ) {
		$this->table_name = $GLOBALS['wpdb']->base_prefix . $table_name;
		$this->page_size  = (int) $page_size;
	}

	/**
	 * @return int
	 */
	public function get_total_items_number() {

		global $wpdb;

		$query = sprintf(
			'SELECT COUNT(*) as amount FROM `%s`',
			$this->table_name
		);

		// @codingStandardsIgnoreLine
		$all = $wpdb->get_results( $query, OBJECT_K );

		return (int) key( $all );
	}

	/**
	 * @param array  $params
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_items( array $params = array(), $type = OBJECT_K ) {

		global $wpdb;

		$default_params = array(
			'page'     => 1,
			'fields'   => array(),
			'where'    => array(),
			'order_by' => array(
				array(
					'field' => 'priority',
					'order' => 'DESC',
				),
				array(
					'field' => 'english_name',
					'order' => 'ASC',
				),
			),
		);
		$params = wp_parse_args( $params, $default_params );

		$select_fields = '';
		if ( ! empty( $params['fields'] ) ) {
			$params['fields'] = esc_sql( $params['fields'] );
			foreach ( $params['fields'] as $field ) {
				// Check for not allowed fields.
				if ( ! isset( $this->fields[ $field ] ) ) {
					continue;
				}

				$select_fields .= ',' . $field;
			}

			// Remove the leading comma.
			if ( isset( $select_fields[0] ) ) {
				$select_fields = substr( $select_fields, 1 );
			}
		}
		if ( '' === $select_fields ) {
			$select_fields = '*';
		}

		$select = "SELECT $select_fields";

		$from = "FROM {$this->table_name}";

		$where = '';
		if ( ! empty( $params['where'] ) ) {
			$where = 'WHERE 1=1 ';
			foreach ( $params['where'] as $search ) {
				if ( empty( $search['field'] ) ) {
					continue;
				}

				$field = $search['field'];

				// Check for not allowed fields.
				if ( ! isset( $this->fields[ $field ] ) ) {
					continue;
				}

				if ( ! isset( $search['compare'] ) ) {
					$search['compare'] = '=';
				} elseif ( ! in_array( $search['compare'], $this->compare_operators, true ) ) {
					continue;
				}

				// @codingStandardsIgnoreStart
				$where .= $wpdb->prepare(
					" AND $field {$search['compare']} {$this->fields[ $field ]}",
					$search['search']
				);
				// @codingStandardsIgnoreEnd
			}
		}

		$order = '';
		if ( ! empty( $params['order_by'] ) ) {
			foreach ( $params['order_by'] as $order_by ) {
				if ( empty( $order_by['field'] ) ) {
					continue;
				}

				$field = $order_by['field'];

				// Check for not allowed fields.
				if ( ! isset( $this->fields[ $field ] ) ) {
					continue;
				}

				$order .= ',' . $field;
				if ( ! empty( $order_by['order'] ) ) {
					$_order_by = strtoupper( $order_by['order'] );
					if ( in_array( $_order_by, array( 'ASC', 'DESC' ), true ) ) {
						$order .= ' ' . $_order_by;
					}
				}
			}

			if ( isset( $order[0] ) ) {
				$order = 'ORDER BY ' . substr( $order, 1 );
			}
		}

		$limit = $this->get_limit( (int) $params['page'] );

		$query = "$select $from $where $order $limit";

		// @codingStandardsIgnoreLine
		$result = $wpdb->get_results( $query, $type );

		return null === $result ? array() : $result;
	}

	/**
	 * @param array  $items
	 * @param string $field_format
	 * @param string $where_format
	 * @return array
	 */
	public function update_items_by_id( array $items, $field_format = '%s', $where_format = '%d' ) {

		global $wpdb;

		$queries = array();

		foreach ( $items as $id => $values ) {
			$wpdb->update(
				$this->table_name,
				(array) $values,
				array(
					'ID' => $id,
				),
				$field_format,
				$where_format
			);
			$queries[ $id ] = $wpdb->last_error;
		}

		return $queries;
	}


	/**
	 * @param array $params
	 * @return void
	 */
	public function insert_item( array $params ) {}

	/**
	 * @param $page_size
	 * @return void
	 */
	public function set_page_size( $page_size ) {
		$this->page_size  = $page_size;
	}

	/**
	 * @return int
	 */
	public function get_page_size() {
		return $this->page_size;
	}

	/**
	 * @param int $page
	 *
	 * @return string
	 */
	private function get_limit( $page ) {

		if ( -1 === $page ) {
			return '';
		}

		$start = $this->page_size * ( $page - 1 );

		return "LIMIT $start, $this->page_size";
	}
}
