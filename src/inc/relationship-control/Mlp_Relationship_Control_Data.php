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
	private $ids = array(
		'source_post_id' => 0,
		'source_site_id' => 0,
		'remote_site_id' => 0,
		'remote_post_id' => 0,
	);

	/**
	 * @var string
	 */
	private $search = '';

	/**
	 * @param array $ids
	 */
	public function __construct( array $ids = array() ) {

		if ( ! empty( $ids ) ) {
			$this->ids = $ids;
			return;
		}

		foreach ( array_keys( $this->ids ) as $key ) {
			$value = filter_input( INPUT_POST, $key );
			if ( null !== $value ) {
				$this->ids[ $key ] = (int) $value;
			}
		}

		$search = (string) filter_input( INPUT_POST, 's' );
		if ( '' !== $search ) {
			$this->search = $search;
		}
	}

	/**
	 * Set values lately.
	 *
	 * @param  array $ids
	 * @return array
	 */
	public function set_ids( array $ids ) {

		$this->ids = $ids;

		return $this->ids;
	}

	/**
	 * @return null|WP_Post
	 */
	public function get_source_post() {

		switch_to_blog( $this->ids['source_site_id'] );

		$post = get_post( $this->ids['source_post_id'] );

		restore_current_blog();

		return $post;
	}

	/**
	 * @return int
	 */
	public function get_remote_site_id() {

		return $this->ids['remote_site_id'];
	}

	public function get_remote_post_id() {

		return $this->ids['remote_post_id'];
	}

	/**
	 * @return array
	 */
	public function get_search_results() {

		if ( 0 === $this->ids['remote_site_id'] || 0 === $this->ids['source_site_id'] ) {
			return array();
		}

		$source_post = $this->get_source_post();

		if ( ! $source_post ) {
			return array();
		}

		$args = array(
			'numberposts' => 10,
			'post_type'   => $source_post->post_type,
			'post_status' => array( 'draft', 'future', 'publish', 'private' ),
		);

		if ( ! empty( $this->ids['remote_post_id'] ) ) {
			$args['exclude'] = $this->ids['remote_post_id'];
		}

		if ( ! empty( $this->search ) ) {
			$args['s'] = $this->search;
			$args['orderby'] = 'relevance';
		}

		switch_to_blog( $this->ids['remote_site_id'] );
		/**
		 * Filters the query arguments for the remote post search.
		 *
		 * @since 2.10.0
		 *
		 * @param array $args Query arguments.
		 */
		$args = (array) apply_filters( 'multilingualpress.remote_post_search_arguments', $args );
		$posts = get_posts( $args );
		restore_current_blog();

		if ( empty( $posts ) ) {
			return array();
		}

		return $posts;
	}
}
