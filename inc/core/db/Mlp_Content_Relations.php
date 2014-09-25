<?php # -*- coding: utf-8 -*-
/**
 * Relationships between content blocks (posts, terms, whatever).
 *
 * @version 2014.08.14
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Content_Relations implements Mlp_Content_Relations_Interface {

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var Mlp_Site_Relations_Interface
	 */
	private $site_relations;

	/**
	 * @var string
	 */
	private $link_table;

	/**
	 * @param wpdb                         $wpdb
	 * @param Mlp_Site_Relations_Interface $site_relations
	 * @param string                       $link_table
	 */
	public function __construct(
		wpdb                         $wpdb,
		Mlp_Site_Relations_Interface $site_relations,
		                             $link_table
	) {

		$this->wpdb = $wpdb;
		$this->site_relations = $site_relations;
		$this->link_table = $link_table;
	}

	/**
	 * Returns an array with site ID as keys and content ID as values.
	 *
	 * @param  int    $source_site_id
	 * @param  int    $source_content_id
	 * @param  string $type
	 * @return array
	 */
	public function get_relations( $source_site_id, $source_content_id, $type = 'post' ) {

		$sql = "
			SELECT
				t.ml_blogid    as site_id,
				t.ml_elementid as content_id
			FROM $this->link_table s
			INNER JOIN $this->link_table t
				ON s.ml_source_blogid    = t.ml_source_blogid &&
				   s.ml_source_elementid = t.ml_source_elementid
			WHERE s.ml_blogid    = %d &&
			      s.ml_elementid = %d
				AND s.ml_type = %s";

		$query   = $this->wpdb->prepare( $sql, $source_site_id, $source_content_id, $type );
		$results = $this->wpdb->get_results( $query, ARRAY_A );

		if ( empty ( $results ) )
			return array();

		$output = array();

		foreach ( $results as $set )
			$output[ (int) $set[ 'site_id' ] ] = (int) $set[ 'content_id' ];

		return $output;
	}

	/**
	 * @param  int    $source_site_id
	 * @param  int    $target_site_id
	 * @param  int    $source_content_id  // post_id or term_taxonomy_id
	 * @param  int    $target_content_id  // the same
	 * @param  string $type
	 * @return bool
	 */
	public function set_relation(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type = 'post'
	) {
		$translation_ids = $this->get_translation_ids(
			$source_site_id,
			$target_site_id,
			$source_content_id,
			$target_content_id,
			$type
		);

		$existing = $this->get_relations( $source_site_id, $source_content_id, $type );

		if ( isset ( $existing[ $target_site_id ] ) ) {

			if ( $existing[ $target_site_id ] === $target_content_id )
				return TRUE;

			$this->delete_relation(
				$translation_ids[ 'ml_source_blogid' ],
				$target_site_id,
				$translation_ids[ 'ml_source_elementid' ],
				0, // old content id
				$type
			);
		}

		$result = (bool) $this->insert_row(
			 $translation_ids[ 'ml_source_blogid' ],
			 $target_site_id,
			 $translation_ids[ 'ml_source_elementid' ],
			 $target_content_id,
			 $type
		);

		do_action(
			'mlp_debug',
			current_filter()
			. '/' . __METHOD__ . '/' . __LINE__
			. " - {$this->wpdb->last_query}"
		);

		return $result;
	}


	/**
	 * @param  int    $source_site_id
	 * @param  int    $target_site_id
	 * @param  int    $source_content_id  post_id or term_taxonomy_id
	 * @param  int    $target_content_id
	 * @param  string $type
	 * @return int                        Number of deleted rows
	 */
	public function delete_relation(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id = 0,
		$type = 'post'
	) {

		$where = array (
			'ml_source_blogid'    => $source_site_id,
			'ml_source_elementid' => $source_content_id,
			'ml_type'             => $type
		);
		$where_format = array (
			'%d',
			'%d',
			'%s'
		);

		if ( 0 < $target_site_id ) {
			$where[ 'ml_blogid' ]    = $target_site_id;
			$where_format[]          = '%d';
		}

		if ( 0 < $target_content_id ) {
			$where[ 'ml_elementid' ] = $target_content_id;
			$where_format[]          = '%d';
		}

		$result = (int) $this->wpdb->delete( $this->link_table, $where, $where_format );

		do_action(
			'mlp_debug',
			current_filter()
			. '/' . __METHOD__ . '/' . __LINE__
			. " - {$this->wpdb->last_query}"
		);

		return $result;
	}

	/**
	 * @param  int    $source_site_id
	 * @param  int    $target_site_id
	 * @param  int    $source_content_id  // post_id or term_taxonomy_id
	 * @param  int    $target_content_id  // the same
	 * @param  string $type
	 * @return array
	 */
	public function get_translation_ids(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type
	) {

		$result = $this->get_existing_translation_ids(
			$source_site_id,
			$target_site_id,
			$source_content_id,
			$target_content_id,
			$type
		);

		if ( empty ( $result ) )
			return $this->get_new_translation_ids( $source_site_id, $source_content_id, $type );

		if ( 1 === count( $result ) )
			return $result[ 0 ];

		// We have more than one id.
		$this->clean_up_duplicated_translation_ids( $result, $type );

		return $result[ 0 ];
	}

	/**
	 * @param  int    $source_site_id
	 * @param  int    $target_site_id
	 * @param  int    $source_content_id  // post_id or term_taxonomy_id
	 * @param  int    $target_content_id  // the same
	 * @param  string $type
	 * @return int
	 */
	private function insert_row(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type
	) {
		$result = (int) $this->wpdb->insert(
			 $this->link_table,
			 array (
				 'ml_source_blogid'    => $source_site_id,
				 'ml_source_elementid' => $source_content_id,
				 'ml_blogid'           => $target_site_id,
				 'ml_elementid'        => $target_content_id,
				 'ml_type'             => $type
			 )
		);

		do_action(
			'mlp_debug',
			current_filter()
			. '/' . __METHOD__ . '/' . __LINE__
			. " - {$this->wpdb->last_query}"
		);

		return $result;
	}

	/**
	 * @param int $source_site_id
	 * @param int $source_content_id
	 * @param string $type
	 * @return array
	 */
	private function get_new_translation_ids( $source_site_id, $source_content_id, $type ) {

		$this->insert_row(
			 $source_site_id,
			 $source_site_id,
			 $source_content_id,
			 $source_content_id,
			 $type
		);


		do_action(
			'mlp_debug',
			current_filter()
			. '/' . __METHOD__ . '/' . __LINE__
			. " - {$this->wpdb->last_query}"
		);

		return array (
			'ml_source_blogid'    => $source_site_id,
			'ml_source_elementid' => $source_content_id
		);
	}

	/**
	 * @param  array $result
	 * @param  string $type
	 * @return int
	 */
	private function clean_up_duplicated_translation_ids( Array $result, $type ) {

		$result = (int) $this->wpdb->update(
		   $this->link_table,
		   array (
			   'ml_source_blogid'    => $result[ 0 ][ 'ml_source_blogid' ],
			   'ml_source_elementid' => $result[ 0 ][ 'ml_source_elementid' ]
		   ),
		   array (
			   'ml_source_blogid'    => $result[ 1 ][ 'ml_source_blogid' ],
			   'ml_source_elementid' => $result[ 1 ][ 'ml_source_elementid' ],
			   'ml_type'             => $type
		   ),
		   array (
			   '%d',
			   '%d'
		   ),
		   array (
			   '%d',
			   '%d',
			   '%s'
		   )
		);

		do_action(
			'mlp_debug',
			current_filter()
				. '/' . __METHOD__ . '/' . __LINE__
				. " - {$this->wpdb->last_query}"
		);

		return $result;
	}

	/**
	 * @param $source_site_id
	 * @param $target_site_id
	 * @param $source_content_id
	 * @param $target_content_id
	 * @param $type
	 * @return mixed
	 */
	private function get_existing_translation_ids( $source_site_id, $target_site_id, $source_content_id, $target_content_id, $type ) {

		$sql = "
			SELECT DISTINCT `ml_source_blogid`, `ml_source_elementid`
			FROM $this->link_table
			WHERE (
				   ( `ml_blogid` = %d AND `ml_elementid` = %d )
				OR ( `ml_blogid` = %d AND `ml_elementid` = %d )
				)
				AND `ml_type` = %s";

		$query = $this->wpdb->prepare(
			$sql,
			$source_site_id,
			$source_content_id,
			$target_site_id,
			$target_content_id,
			$type
		);

		$result = $this->wpdb->get_results( $query, ARRAY_A );

		return $result;
	}
}
