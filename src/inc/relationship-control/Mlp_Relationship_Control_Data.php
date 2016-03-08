<?php

/**
 * Class Mlp_Relationship_Control_Data
 *
 * @author  Inpsyde GmbH, toscho
 * @version 2014.03.14
 * @license GPL
 */
class Mlp_Relationship_Control_Data {

	/**
	 * @var array
	 */
	private $ids = array (
		'source_post_id' => 0,
		'source_site_id' => 0,
		'remote_site_id' => 0,
		'remote_post_id' => 0
	);

	/**
	 * @var string
	 */
	private $search = '';

	/**
	 * @param array $ids
	 */
	public function __construct( Array $ids = array () ) {

		if ( ! empty ( $ids ) ) {
			$this->ids = $ids;
			return;
		}

		foreach ( $this->ids as $id => $value ) {
			if ( isset ( $_REQUEST[ $id ] ) )
				$this->ids[ $id ] = (int) $_REQUEST[ $id ];
		}

		if ( isset ( $_REQUEST['s'] ) )
			$this->search = $_REQUEST['s'];
	}

	/**
	 * Set values lately.
	 *
	 * @param  array $ids
	 * @return array
	 */
	public function set_ids( Array $ids ) {

		$this->ids = $ids;

		return $this->ids;
	}

	/**
	 * @return null|WP_Post
	 */
	public function get_source_post() {

		switch_to_blog( $this->ids[ 'source_site_id' ] );

		$post = get_post( $this->ids[ 'source_post_id' ] );

		restore_current_blog();

		return $post;
	}

	/**
	 * @return int
	 */
	public function get_remote_site_id() {

		return $this->ids[ 'remote_site_id' ];
	}

	public function get_remote_post_id() {

		return $this->ids[ 'remote_post_id' ];
	}

	/**
	 * @return array
	 */
	public function get_search_results() {

		if ( 0 === $this->ids[ 'remote_site_id' ]
			or 0 === $this->ids[ 'source_site_id' ]
		)
			return array ();

		$source_post = $this->get_source_post();

		if ( ! $source_post )
			return array ();

		$args = array (
			'numberposts' => 10,
			'post_type'   => $source_post->post_type,
			'post_status' => array ( 'draft', 'publish', 'private' )
		);

		if ( ! empty ( $this->ids[ 'remote_post_id' ] ) )
			$args[ 'exclude' ] = $this->ids[ 'remote_post_id' ];

		if ( ! empty( $this->search ) ) {
			$args['s'] = $this->search;
			$args['orderby'] = 'relevance';
		}

		switch_to_blog( $this->ids[ 'remote_site_id' ] );
		$posts = get_posts( $args );
		restore_current_blog();

		if ( empty ( $posts ) )
			return array ();

		return $posts;
	}
}
