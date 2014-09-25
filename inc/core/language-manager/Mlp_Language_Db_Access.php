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
            'native_name' =>  '%s',
            'custom_name' => '%s',
            'is_rtl' => '%d',
            'iso_639_1' => '%s',
            'iso_639_2' => '%s',
            'wp_locale' => '%s',
            'http_name' => '%s',
            'priority' => '%d',
	);

	/**
	 * @param     $table_name
	 * @param int $page_size
	 */
	public function __construct( $table_name, $page_size = 20 ) {
		$this->table_name = $GLOBALS['wpdb']->base_prefix . $table_name;
		$this->page_size  = $page_size;
	}

	/**
	 * @return int
	 */
	public function get_total_items_number() {

		global $wpdb;

		$all = $wpdb->get_results(
			"SELECT COUNT(*) as amount FROM `{$this->table_name}`",
			OBJECT_K // Makes the result the first and only key.
		);
		return (int) key( $all );
	}

	/**
	 * @param   Array $params
	 * @param   String $type
	 * @return  Array $results
	 */
	public function get_items( array $params = array(), $type = OBJECT_K ) {
		global $wpdb;

		$default_params = array(
			'page'      => 1,
		    'fields'    => array(),
			'where'     => array(),
		    'order_by'  => array(
			    array(
				    'field' => 'priority',
					'order' => 'DESC'
			    ),
			    array(
				    'field' => 'english_name',
			        'order' => 'ASC'
			    )
		    )
		);
		// merge defaults with the given values
		$params = wp_parse_args( $params, $default_params );

		// the SELECT-part of the statement
		$select_fields = '';
		if( !empty( $params[ 'fields' ] ) ){
			$i = 0;
			foreach( $params[ 'fields' ] as $field ){
				if( $i > 0 ){
					$select_fields .= ', ';
				}
				// check for not allowed fields
				if( !array_key_exists( $field, $this->fields ) ){
					$i = $i - 1;
					continue;
				}
				$select_fields .= $field;
				$i = $i + 1;
			}
		}
		// adding SELECT ALL to query if no specific value is given
		if( $select_fields === '' ){
			$select_fields = '*';
		}

		$select = ' SELECT ' . $select_fields;

		// the "FROM {table}"
		$from = 'FROM ' . $this->table_name;

		// the WHERE-Clause
		$where = '';
		if( !empty( $params[ 'where' ] ) ){
			$where .= 'WHERE 1=1 ';
			foreach( $params[ 'where' ] as $search ){
				if( !isset( $search[ 'compare' ] ) ){
					$search[ 'compare' ] = '=';
				}
				$field = $search[ 'field' ];
				// check for not allowed fields
				if( !array_key_exists( $field, $this->fields ) ){
					continue;
				}
				$where .= $wpdb->prepare(
					' AND ' . $field . ' ' . $search[ 'compare' ] . ' ' . $this->fields[ $field ],
					$search[ 'search' ]
				);
			}
		}

		// the ORDER BY statement
		$order = '';
		if( !empty( $params[ 'order_by' ] ) ){
			$order .= 'ORDER BY ';
			$i = 0;
			foreach( $params[ 'order_by' ] as $order_by ){
				if( $i > 0 ){
					$order .= ', ';
				}

				// check for not allowed fields
				if( !array_key_exists( $order_by[ 'field' ], $this->fields ) ){
					$i = $i - 1;
					continue;
				}
				$order .= $order_by[ 'field' ];
				if( isset( $order_by[ 'order' ] ) ){
					$order .= ' ' . $order_by[ 'order' ];
				}
				$i = $i + 1;
			}
		}

		// the limit
		$limit  = $this->get_limit( $params[ 'page' ] );

		$query  = '';
		$query .= $select . " ";
		$query .= $from . " ";
		$query .= $where . " ";
		$query .= $order . " ";
		$query .= $limit;

		$result = $wpdb->get_results( $query, $type );

		return NULL === $result ? array() : $result;
	}

	/**
	 * @param array  $items
	 * @param string $field_format
	 * @param string $where_format
	 * @return array
	 */
	public function update_items_by_id( Array $items, $field_format = '%s', $where_format = '%d' ) {

		global $wpdb;

		$queries = array();

		foreach ( $items as $id => $values ) {
			$wpdb->update(
				$this->table_name,
				(array) $values,
				array( 'ID' => $id ),
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
	public function insert_item( Array $params ) {}

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
	 * @param $page
	 * @return string
	 */
	private function get_limit( $page ) {

		if ( -1 === $page )
			return '';

		$start    = $this->page_size * ( $page - 1 );

		return "\nLIMIT $start, $this->page_size";
	}
}