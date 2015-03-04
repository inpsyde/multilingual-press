<?php

/**
 * Class Mlp_Relationship_Changer
 *
 * Changes post relationships on AJAX calls.
 *
 * @version 2014.07.23
 * @author  Inpszde GmbH, toscho
 * @license GPL
 */
class Mlp_Relationship_Changer {

	/**
	 * @var int
	 */
	private $source_post_id = 0;

	/**
	 * @var int
	 */
	private $source_blog_id = 0;

	/**
	 * @var int
	 */
	private $remote_post_id = 0;

	/**
	 * @var int
	 */
	private $remote_blog_id = 0;

	/**
	 * @var int
	 */
	private $new_post_id    = 0;

	/**
	 * @var string
	 */
	private $new_post_title = '';

	/**
	 * @type Mlp_Content_Relations_Interface
	 */
	private $content_relations;

	/**
	 * @param Inpsyde_Property_List_Interface $data
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->content_relations = $data->content_relations;
		$this->prepare_values();
	}

	/**
	 * @return int|string
	 */
	public function new_relation() {

		switch_to_blog( $this->source_blog_id );

		$source_post = get_post( $this->source_post_id );

		restore_current_blog();

		if ( ! $source_post )
			return 'source not found';

		do_action( 'mlp_before_post_synchronization' );

		switch_to_blog( $this->remote_blog_id );

		$post_id = wp_insert_post(
			array (
				'post_type'   => $source_post->post_type,
				'post_status' => 'draft',
				'post_title'  => $this->new_post_title
			),
			TRUE
		);

		restore_current_blog();

		do_action( 'mlp_after_post_synchronization' );

		if ( is_a( $post_id, 'WP_Error' ) )
			return $post_id->get_error_messages();

		$this->new_post_id = $post_id;

		$this->connect_existing();

		return $this->new_post_id;
	}

	/**
	 * Get the real current post type.
	 *
	 * Includes workaround for auto-drafts.
	 *
	 * @param  WP_Post $post
	 * @return string
	 */
	public function get_real_post_type( WP_Post $post ) {

		if ( 'revision' !== $post->post_type )
			return $post->post_type;

		if ( empty ( $_POST[ 'post_type' ] ) )
			return $post->post_type;

		if ( 'revision' === $_POST[ 'post_type' ] )
			return $post->post_type;

		if ( is_string( $_POST[ 'post_type' ] ) )
			return $_POST[ 'post_type' ]; // auto-draft

		return $post->post_type;
	}

	/**
	 * @return false|int
	 */
	public function connect_existing() {

		$this->disconnect();
		return $this->create_new_relation();

	}

	/**
	 * @return bool
	 */
	private function create_new_relation() {

		return $this->content_relations->set_relation(
			$this->source_blog_id,
			$this->remote_blog_id,
			$this->source_post_id,
			$this->new_post_id,
			'post'
		);
	}

	/**
	 * @return false|int
	 */
	public function disconnect() {

		return $this->content_relations->delete_relation(
			$this->source_blog_id,
			$this->remote_blog_id,
			$this->source_post_id,
			$this->remote_post_id,
			'post'
		);
	}

	/**
	 * Fill default values.
	 *
	 * @return void
	 */
	private function prepare_values() {

		$find = array (
			'source_post_id',
			'source_blog_id',
			'remote_post_id',
			'remote_blog_id',
			'new_post_id',
			'new_post_title',
		);

		foreach ( $find as $value ) {
			if ( ! empty ( $_REQUEST[ $value ] ) ) {

				if ( 'new_post_title' === $value )
					$this->$value = (string) $_REQUEST[ $value ];
				else
					$this->$value = (int) $_REQUEST[ $value ];
			}
		}
	}
}