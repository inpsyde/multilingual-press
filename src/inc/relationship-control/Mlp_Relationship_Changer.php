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
	private $source_site_id = 0;

	/**
	 * @var int
	 */
	private $remote_post_id = 0;

	/**
	 * @var int
	 */
	private $remote_site_id = 0;

	/**
	 * @var int
	 */
	private $relation_post_id = 0;

	/**
	 * @var int
	 */
	private $relation_site_id = 0;

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

		$this->content_relations = $data->get( 'content_relations' );
		$this->prepare_values();
	}

	/**
	 * @return int|string
	 */
	public function new_relation() {

		switch_to_blog( $this->source_site_id );

		$source_post = get_post( $this->source_post_id );

		restore_current_blog();

		if ( ! $source_post )
			return 'source not found';

		$save_context = array(
			'source_blog'    => $this->source_site_id,
			'source_post'    => $source_post,
			'real_post_type' => $this->get_real_post_type( $source_post ),
			'real_post_id'   => $this->get_real_post_id( $this->source_post_id ),
		);

		/** This action is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
		do_action( 'mlp_before_post_synchronization', $save_context );

		switch_to_blog( $this->remote_site_id );

		$post_id = wp_insert_post(
			array (
				'post_type'   => $source_post->post_type,
				'post_status' => 'draft',
				'post_title'  => $this->new_post_title
			),
			TRUE
		);

		restore_current_blog();

		$save_context[ 'target_blog_id' ] = $this->remote_site_id;

		/** This action is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
		do_action( 'mlp_after_post_synchronization', $save_context );

		if ( is_wp_error( $post_id ) )
			return $post_id->get_error_message();

		$this->remote_post_id = $post_id;

		$this->connect_existing();

		return $this->remote_post_id;
	}

	/**
	 * Target callback for the AJAX request for connecting a new post.
	 *
	 * @return int|string
	 */
	public function new_post() {

		$this->prepare_relation_data();

		return $this->new_relation();
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
	 * Figure out the post ID.
	 *
	 * Inspects POST request data and too, because we get two IDs on auto-drafts.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return int
	 */
	public function get_real_post_id( $post_id ) {

		if ( ! empty( $_POST[ 'post_ID' ] ) ) {
			return (int) $_POST[ 'post_ID' ];
		}

		return $post_id;
	}

	/**
	 * @return false|int
	 */
	public function connect_existing() {

		return $this->content_relations->set_relation(
			$this->source_site_id,
			$this->remote_site_id,
			$this->source_post_id,
			$this->remote_post_id,
			'post'
		);
	}

	/**
	 * Target callback for the AJAX request for connecting an existing post.
	 *
	 * @return false|int
	 */
	public function search_post() {

		$this->prepare_relation_data();

		return $this->connect_existing();
	}

	/**
	 * @return int
	 */
	public function disconnect() {

		$relation_site_id = $this->relation_site_id;

		$target_site_id = $this->remote_site_id;
		if ( $target_site_id === $relation_site_id ) {
			$target_site_id = $this->source_site_id;
		}

		$relations = $this->content_relations->get_relations(
			$relation_site_id,
			$this->relation_post_id,
			'post'
		);
		if ( empty( $relations[ $target_site_id ] ) ) {
			return 0;
		}

		return $this->content_relations->delete_relation(
			$relation_site_id,
			2 < count( $relations ) ? $target_site_id : 0,
			$this->relation_post_id,
			0,
			'post'
		);
	}

	/**
	 * Target callback for the AJAX request for disconnecting the currently connected post.
	 *
	 * @return int
	 */
	public function disconnect_post() {

		$this->prepare_relation_data();

		return $this->disconnect();
	}

	/**
	 * Reads the data from the request and sets up the correct relation data.
	 *
	 * @return void
	 */
	private function prepare_relation_data() {

		$translation_ids = $this->content_relations->get_translation_ids(
			$this->source_site_id,
			$this->remote_site_id,
			$this->source_post_id,
			$this->remote_post_id,
			'post'
		);
		if ( $translation_ids ) {
			$this->relation_site_id = $translation_ids['ml_source_blogid'];

			$this->relation_post_id = $translation_ids['ml_source_elementid'];
		}
	}

	/**
	 * Fill default values.
	 *
	 * @return void
	 */
	private function prepare_values() {

		$find = array (
			'source_post_id',
			'source_site_id',
			'remote_post_id',
			'remote_site_id',
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
