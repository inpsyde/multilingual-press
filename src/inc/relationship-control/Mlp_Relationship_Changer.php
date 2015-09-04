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

		$this->content_relations = $data->get( 'content_relations' );
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

		$save_context = array(
			'source_blog'    => $this->source_blog_id,
			'source_post'    => $source_post,
			'real_post_type' => $this->get_real_post_type( $source_post ),
			'real_post_id'   => $this->get_real_post_id( $this->source_post_id ),
		);

		/** This action is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
		do_action( 'mlp_before_post_synchronization', $save_context );

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

		$save_context[ 'target_blog_id' ] = $this->remote_blog_id;

		/** This action is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
		do_action( 'mlp_after_post_synchronization', $save_context );

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
	 * @return int
	 */
	public function disconnect() {

		$translation_ids = $this->content_relations->get_translation_ids(
			$this->source_blog_id,
			$this->remote_blog_id,
			$this->source_post_id,
			$this->remote_post_id,
			'post'
		);

		$remote_blog_id = $this->remote_blog_id;

		$remote_post_id = $this->remote_post_id;

		if ( $translation_ids[ 'ml_source_blogid' ] !== $this->source_blog_id ) {
			$remote_blog_id = $this->source_blog_id;
			if ( 0 !== $this->remote_post_id ) {
				$remote_post_id = $this->source_post_id;
			}
		}

		return $this->content_relations->delete_relation(
			$translation_ids[ 'ml_source_blogid' ],
			$remote_blog_id,
			$translation_ids[ 'ml_source_elementid' ],
			$remote_post_id,
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
