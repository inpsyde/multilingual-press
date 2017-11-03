<?php # -*- coding: utf-8 -*-

/**
 * Data model for post translation. Handles inserts of new posts only.
 */
class Mlp_Translatable_Post_Data implements Mlp_Translatable_Post_Data_Interface, Mlp_Save_Post_Interface {

	/**
	 * @var array
	 */
	private $allowed_post_types;

	/**
	 * @var Mlp_Content_Relations_Interface
	 */
	private $content_relations;

	/**
	 * @var string
	 */
	private $link_table;

	/**
	 * @var string
	 */
	private $name_base = 'mlp_to_translate';

	/**
	 * @var array
	 */
	private $parent_elements = array();

	/**
	 * @var array
	 */
	private $post_request_data = array();

	/**
	 * @var array
	 */
	public $save_context = array();

	/**
	 * @var int
	 */
	private $source_site_id;

	/**
	 * @param                                 $deprecated
	 * @param array                           $allowed_post_types
	 * @param string                          $link_table
	 * @param Mlp_Content_Relations_Interface $content_relations
	 */
	public function __construct(
		$deprecated,
		array $allowed_post_types,
		$link_table,
		Mlp_Content_Relations_Interface $content_relations
	) {

		$this->allowed_post_types = $allowed_post_types;

		$this->link_table = $link_table;

		$this->content_relations = $content_relations;

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$this->post_request_data = (array) filter_input_array( INPUT_POST, FILTER_DEFAULT, false );
		}

		$this->source_site_id = get_current_blog_id();
	}

	/**
	 * @param  WP_Post $source_post
	 * @param  int     $blog_id
	 *
	 * @return WP_Post
	 */
	public function get_remote_post( WP_Post $source_post, $blog_id ) {

		$post = null;

		$linked = Mlp_Helpers::load_linked_elements( $source_post->ID, '', get_current_blog_id() );
		if ( ! empty( $linked[ $blog_id ] ) && blog_exists( $blog_id ) ) {
			$post = get_blog_post( $blog_id, $linked[ $blog_id ] );
		}

		if ( $post ) {
			return $post;
		}

		return $this->get_dummy_post( $source_post->post_type );
	}

	/**
	 * @param int     $post_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function save( $post_id, WP_Post $post ) {

		if ( ! $this->is_valid_save_request( $post ) ) {
			return;
		}

		$post_id = $this->get_real_post_id( $post_id );

		$this->save_context = array(
			'source_blog'    => get_current_blog_id(),
			'source_post'    => $post,
			'real_post_type' => $this->get_real_post_type( $post ),
			'real_post_id'   => $post_id,
		);

		// Get the post
		$post_data = get_post( $post_id, ARRAY_A );
		$post_meta = $this->get_post_meta_to_transfer();

		/** This filter is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
		$post_data = apply_filters( 'mlp_pre_save_post', $post_data, $this->save_context );
		if ( ! $post_data || ! is_array( $post_data ) ) {
			return;
		}

		$file     = '';
		$path     = '';
		$fileinfo = array();

		// Check for thumbnail
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			$thumb_id = get_post_thumbnail_id( $post_id );
			if ( 0 < $thumb_id ) {
				$path     = wp_upload_dir();
				$file     = (string) get_post_meta( $thumb_id, '_wp_attached_file', true );
				$fileinfo = pathinfo( $file );
				include_once( ABSPATH . 'wp-admin/includes/image.php' ); //including the attachment function
			}
		}
		// Create the post array
		$new_post = array(
			'post_title'   => $post_data['post_title'],
			'post_content' => $post_data['post_content'],
			'post_status'  => 'draft',
			'post_author'  => $post_data['post_author'],
			'post_excerpt' => $post_data['post_excerpt'],
			'post_date'    => $post_data['post_date'],
			'post_type'    => $post_data['post_type'],
		);

		$this->find_post_parents( $post_data['post_type'], $post->post_parent );

		/** This action is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
		do_action( 'mlp_before_post_synchronization', $this->save_context );

		// Create a copy of the item for every related blog
		foreach ( $this->post_request_data[ $this->name_base ] as $blog_id ) {
			if ( get_current_blog_id() === (int) $blog_id || ! blog_exists( $blog_id ) ) {
				continue;
			}

			$nonce_validator = Mlp_Nonce_Validator_Factory::create(
				"save_translation_of_post_{$post_id}_for_site_{$blog_id}",
				$this->source_site_id
			);

			$request_validator = Mlp_Save_Post_Request_Validator_Factory::create( $nonce_validator );
			if ( ! $request_validator->is_valid( $post ) ) {
				continue;
			}

			switch_to_blog( $blog_id );

			// Set the linked parent post
			$new_post['post_parent'] = $this->get_post_parent( $blog_id );

			$this->save_context['target_blog_id'] = $blog_id;

			/** This filter is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
			$new_post = apply_filters( 'mlp_pre_insert_post', $new_post, $this->save_context );

			// Insert remote blog post
			$remote_post_id = wp_insert_post( wp_slash( $new_post ) );

			if ( ! empty( $post_meta ) ) {
				$this->update_remote_post_meta( $remote_post_id, $post_meta );
			}

			if ( '' !== $file ) { // thumbfile exists
				if ( 0 < count( $fileinfo ) ) {
					$filedir  = wp_upload_dir();
					$filename = wp_unique_filename( $filedir['path'], $fileinfo['basename'] );
					$copy     = copy( $path['basedir'] . '/' . $file, $filedir['path'] . '/' . $filename );

					if ( $copy ) {
						$wp_filetype = wp_check_filetype( $filedir['url'] . '/' . $filename ); //get the file type
						$attachment  = array(
							'post_mime_type' => $wp_filetype['type'],
							'guid'           => $filedir['url'] . '/' . $filename,
							'post_parent'    => $remote_post_id,
							'post_title'     => '',
							'post_excerpt'   => '',
							'post_author'    => $post_data['post_author'],
							'post_content'   => '',
						);

						//insert the image
						$attach_id = wp_insert_attachment( $attachment, $filedir['path'] . '/' . $filename );
						if ( ! is_wp_error( $attach_id ) ) {
							wp_update_attachment_metadata(
								$attach_id,
								wp_generate_attachment_metadata( $attach_id, $filedir['path'] . '/' . $filename )
							);
							// update the image data
							set_post_thumbnail( $remote_post_id, $attach_id );
						}
					}
				}
			}
			$this->set_linked_element( $post_id, $blog_id, $remote_post_id );

			restore_current_blog();
		}

		/** This action is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
		do_action( 'mlp_after_post_synchronization', $this->save_context );
	}

	/**
	 * @param string $post_type
	 * @param int    $post_parent
	 *
	 * @return void
	 */
	public function find_post_parents( $post_type, $post_parent ) {

		if ( ! is_post_type_hierarchical( $post_type ) ) {
			return;
		}

		if ( 0 < $post_parent ) {
			$this->parent_elements = mlp_get_linked_elements( $post_parent );
		}
	}

	/**
	 * @param $blog_id
	 *
	 * @return int
	 */
	public function get_post_parent( $blog_id ) {

		if ( empty( $this->parent_elements ) ) {
			return 0;
		}

		if ( empty( $this->parent_elements[ $blog_id ] ) ) {
			return 0;
		}

		return $this->parent_elements[ $blog_id ];
	}

	/**
	 * Figure out the post ID.
	 *
	 * Inspects POST request data and too, because we get two IDs on auto-drafts.
	 *
	 * @param  int $post_id
	 *
	 * @return int
	 */
	public function get_real_post_id( $post_id ) {

		if ( ! empty( $this->post_request_data['post_ID'] ) ) {
			return (int) $this->post_request_data['post_ID'];
		}

		return $post_id;
	}

	/**
	 * set the source id of the element
	 *
	 * @param   int $source_content_id ID of current element
	 * @param   int $remote_site_id    ID of remote site
	 * @param   int $remote_content_id ID of remote content
	 *
	 * @return  void
	 */
	public function set_linked_element( $source_content_id, $remote_site_id, $remote_content_id ) {

		$this->content_relations->set_relation(
			$this->source_site_id,
			$remote_site_id,
			$source_content_id,
			$remote_content_id,
			'post'
		);
	}

	/**
	 * Add source post meta to remote post.
	 *
	 * @param  int   $remote_post_id
	 * @param  array $post_meta
	 *
	 * @return void
	 */
	public function update_remote_post_meta( $remote_post_id, $post_meta = array() ) {

		/**
		 * Filter post meta data before saving.
		 *
		 * @param array $post_meta    Post meta data.
		 * @param array $save_context Context of the to-be-saved post.
		 */
		$new_post_meta = apply_filters( 'mlp_pre_insert_post_meta', $post_meta, $this->save_context );

		if ( empty( $new_post_meta ) ) {
			return;
		}

		foreach ( $new_post_meta as $key => $value ) {
			update_post_meta( $remote_post_id, $key, $value );
		}
	}

	/**
	 * Return filtered array of post meta data.
	 *
	 * This function has changed in version 2.1: In earlier versions, we have
	 * just used all available post meta keys. That raised too many
	 * compatibility issues with other plugins and some themes, so we use an
	 * empty array now. If you want to synchronize post meta data, you have to
	 * opt-in per filter.
	 *
	 * @return array
	 */
	public function get_post_meta_to_transfer() {

		/**
		 * Filter the to-be-synchronized post meta fields.
		 *
		 * @param array $post_meta    Post meta fields.
		 * @param array $save_context Context of the to-be-saved post.
		 */
		$post_meta = apply_filters( 'mlp_pre_save_post_meta', array(), $this->save_context );

		return $post_meta;
	}

	/**
	 * Get the real current post type.
	 *
	 * Includes workaround for auto-drafts.
	 *
	 * @param  WP_Post $post
	 *
	 * @return string
	 */
	public function get_real_post_type( WP_Post $post ) {

		$post_id = $post->ID;

		static $post_type = array();
		if ( isset( $post_type[ $post_id ] ) ) {
			return $post_type[ $post_id ];
		}

		if (
			'revision' === $post->post_type
			&& ! empty( $this->post_request_data['post_type'] )
			&& is_string( $this->post_request_data['post_type'] )
			&& 'revision' !== $this->post_request_data['post_type']
		) {
			$post_type[ $post_id ] = $this->post_request_data['post_type'];
		} else {
			$post_type[ $post_id ] = $post->post_type;
		}

		return $post_type[ $post_id ];
	}

	/**
	 * @param  string $post_type
	 *
	 * @return WP_Post
	 */
	public function get_dummy_post( $post_type ) {

		return new WP_Post( (object) array(
			'post_type' => $post_type,
			'dummy'     => true,
		) );
	}

	/**
	 * @param  int $blog_id
	 *
	 * @return string
	 */
	public function get_remote_language( $blog_id ) {

		static $blogs = false;

		if ( ! $blogs ) {
			$blogs = get_site_option( 'inpsyde_multilingual' );
		}

		$language = '(' . $blog_id . ')';

		if ( empty( $blogs[ $blog_id ] ) ) {
			return $language;
		}

		$data = $blogs[ $blog_id ];

		if ( ! empty( $data['text'] ) ) {
			return $data['text'];
		}

		if ( ! empty( $data['lang'] ) ) {
			return $data['lang'];
		}

		return $language;
	}

	/**
	 * Set the context of the to-be-saved post.
	 *
	 * @param array $save_context Save context.
	 *
	 * @return void
	 */
	public function set_save_context( array $save_context = array() ) {

		$this->save_context = $save_context;
	}

	/**
	 * Check if the current request should be processed by save().
	 *
	 * @param WP_Post $post
	 * @param string  $name_base
	 *
	 * @return bool
	 */
	public function is_valid_save_request( WP_Post $post, $name_base = '' ) {

		$name_base = $name_base ? (string) $name_base : $this->name_base;

		static $called = 0;

		if ( ms_is_switched() ) {
			return false;
		}

		// For auto-drafts, 'save_post' is called twice, resulting in doubled drafts for translations.
		$called ++;

		if ( ! empty( $this->post_request_data['wp-preview'] ) ) {
			return false;
		}

		if ( ! in_array( $this->get_real_post_type( $post ), $this->allowed_post_types, true ) ) {
			return false;
		}

		if (
			empty( $this->post_request_data[ $name_base ] )
			|| ! is_array( $this->post_request_data[ $name_base ] )
		) {
			return false;
		}

		if (
			! empty( $this->post_request_data['original_post_status'] )
			&& 'auto-draft' === $this->post_request_data['original_post_status']
			&& 1 < $called
		) {
			return false;
		}

		// We only need this when the post is published or drafted.
		if ( ! $this->is_connectable_status( $post ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check post status.
	 *
	 * Includes special hacks for auto-drafts.
	 *
	 * @param  WP_Post $post
	 *
	 * @return bool
	 */
	private function is_connectable_status( WP_Post $post ) {

		if ( in_array( $post->post_status, array( 'publish', 'draft', 'private', 'auto-draft', 'future' ), true ) ) {
			return true;
		}

		return $this->is_auto_draft( $post, $this->post_request_data );
	}

	/**
	 * Check for hidden auto-draft
	 *
	 * Auto-drafts are sent as revision with a status 'inherit'.
	 * We have to inspect the $_POST array($request) to distinguish them from
	 * real revisions and attachments (which have the same status)
	 *
	 * @param  WP_Post $post
	 * @param  array   $request Usually (a copy of) $_POST
	 *
	 * @return bool
	 */
	private function is_auto_draft( WP_Post $post, array $request ) {

		if ( 'inherit' !== $post->post_status ) {
			return false;
		}

		if ( 'revision' !== $post->post_type ) {
			return false;
		}

		if ( empty( $request['original_post_status'] ) ) {
			return false;
		}

		return 'auto-draft' === $request['original_post_status'];
	}
}
