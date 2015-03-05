<?php
/**
 * Class Mlp_Translatable_Post_Data
 *
 * Data model for post translation. Handles inserts of new posts only.
 *
 * @version 2014.03.14
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Translatable_Post_Data
	implements Mlp_Translatable_Post_Data_Interface,
			   Mlp_Save_Post_Interface {

	/**
	 * @var Mlp_Request_Validator_Interface
	 */
	private $request_validator;

	/**
	 * @var array
	 */
	private $allowed_post_types;

	/**
	 * @var string
	 */
	private $link_table;

	/**
	 * @var array
	 */
	private $post_request_data = array();

	/**
	 * @var array
	 */
	private $parent_elements = array ();

	/**
	 * Context for hook on save.
	 *
	 * @var array
	 */
	public $save_context = array ();

	/**
	 *
	 *
	 * @type Mlp_Content_Relations_Interface
	 */
	private $content_relations;

	/**
	 * @param Mlp_Request_Validator_Interface $request_validator
	 * @param array $allowed_post_types
	 * @param string $link_table
	 * @param Mlp_Content_Relations_Interface $content_relations
	 */
	function __construct(
		Mlp_Request_Validator_Interface $request_validator,
		Array                           $allowed_post_types,
	                                    $link_table,
		Mlp_Content_Relations_Interface $content_relations
	) {
		$this->request_validator  = $request_validator;
		$this->allowed_post_types = $allowed_post_types;
		$this->link_table         = $link_table;

		if ( 'POST' === $_SERVER[ 'REQUEST_METHOD' ] )
			$this->post_request_data = $_POST;

		$this->source_site_id = get_current_blog_id();
		$this->content_relations = $content_relations;
	}

	/**
	 * @param  WP_Post $source_post
	 * @param  int     $blog_id
	 * @return WP_Post
	 */
	public function get_remote_post( WP_Post $source_post, $blog_id ) {

		$post   = NULL;
		$linked = Mlp_Helpers::load_linked_elements( $source_post->ID, '', get_current_blog_id() );

		if ( ! empty ( $linked[ $blog_id ] ) && blog_exists( $blog_id ) ) {
			$post = get_blog_post( $blog_id, $linked[ $blog_id ] );
		}

		if ( $post )
			return $post;

		return $this->get_dummy_post( $source_post->post_type );

	}

	/**
	 * @param int     $post_id
	 * @param WP_Post $post
	 * @return void
	 */
	public function save( $post_id, WP_Post $post ) {

		if ( ! $this->request_validator->is_valid( $post ) )
			return;

		$post_type = $this->get_real_post_type( $post );
		$post_id   = $this->get_real_post_id( $post_id );

		if ( ! in_array( $post_type, $this->allowed_post_types ) )
			return;

		if ( empty ( $this->post_request_data[ 'mlp_to_translate' ] ) )
			return;

		$to_translate = $this->post_request_data[ 'mlp_to_translate' ];

		$this->save_context = array (
			'source_blog'    => get_current_blog_id(),
			'source_post'    => $post,
			'real_post_type' => $post_type,
			'real_post_id'   => $post_id
		);

		// Get the post
		$post_data  = get_post( $post_id, ARRAY_A );
		$post_meta = $this->get_post_meta_to_transfer( $post_id );

		// checking if "mlp_pre_save_postdata" has an filter and showing the deprecated message
		if( has_filter( 'mlp_pre_save_postdata' ) ){
			_doing_it_wrong(
				'mlp_pre_save_postdata',
				'mlp_pre_save_postdata is deprecated and will be removed in MultilingualPress 2.2, please use mlp_pre_save_post instead.',
				'2.1'
			);
		}
		/**
		 * Pre-Filter before Saving the Post
		 * @param       Array $post_data
		 * @param       Array $save_context
		 * @deprecated
		 * @see         mlp_pre_save_post
		 */
		$post_data = apply_filters( 'mlp_pre_save_postdata', $post_data, $this->save_context );

		/**
		 * Pre-Filter before Saving the Post
		 * @param   Array $post_data
		 * @param   Array $save_context
		 */
		$post_data = apply_filters( 'mlp_pre_save_post', $post_data, $this->save_context );

		// When the filter returns FALSE, we'll stop here
		if ( FALSE == $post_data || ! is_array( $post_data ) )
			return;

		$file = $path = '';
		$fileinfo = array ();

		// Check for thumbnail
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			$thumb_id = get_post_thumbnail_id( $post_id );
			if ( 0 < $thumb_id ) {
				$path = wp_upload_dir();
				$file = get_post_meta( $thumb_id, '_wp_attached_file', TRUE );
				$fileinfo = pathinfo( $file );
				include_once( ABSPATH . 'wp-admin/includes/image.php' ); //including the attachment function
			}
		}
		// Create the post array
		$new_post = array (
			'post_title'	=> $post_data[ 'post_title' ],
			'post_content'	=> $post_data[ 'post_content' ],
			'post_status'	=> 'draft',
			'post_author'	=> $post_data[ 'post_author' ],
			'post_excerpt'	=> $post_data[ 'post_excerpt' ],
			'post_date'		=> $post_data[ 'post_date' ],
			'post_type'		=> $post_data[ 'post_type' ]
		);

		$this->find_post_parents( $post_data[ 'post_type' ], $post->post_parent );

		/**
		 * Run before the first save_post action is called in other blogs.
		 *
		 * @param Array $save_context
		 */
		do_action( 'mlp_before_post_synchronization', $this->save_context );

		// Create a copy of the item for every related blog
		foreach ( $to_translate as $blog_id ) {

			if ( $blog_id == get_current_blog_id() or ! blog_exists( $blog_id ) )
				continue;

			switch_to_blog( $blog_id );

			// Set the linked parent post
			$new_post[ 'post_parent' ] = $this->get_post_parent( $blog_id );

			$this->save_context[ 'target_blog_id' ] = $blog_id;

			/**
			 * Filter post data before it is saved to the database.
			 *
			 * @param array $new_post
			 * @param array $context
			 */
			$new_post = apply_filters(
				'mlp_pre_insert_post',
				$new_post,
				$this->save_context
			);

			// Insert remote blog post
			$remote_post_id = wp_insert_post( $new_post );
			//echo $remote_post_id . '<br>';

			if ( ! empty ( $post_meta ) )
				$this->update_remote_post_meta( $remote_post_id, $post_meta );

			if ( '' != $file ) { // thumbfile exists

				if ( 0 < count( $fileinfo ) ) {

					$filedir = wp_upload_dir();
					$filename = wp_unique_filename( $filedir[ 'path' ], $fileinfo[ 'basename' ] );
					$copy = copy( $path[ 'basedir' ] . '/' . $file, $filedir[ 'path' ] . '/' . $filename );

					if ( $copy ) {

						$wp_filetype = wp_check_filetype( $filedir[ 'url' ] . '/' . $filename ); //get the file type
						$attachment = array(
							'post_mime_type'	=> $wp_filetype[ 'type' ],
							'guid'				=> $filedir[ 'url' ] . '/' . $filename,
							'post_parent'		=> $remote_post_id,
							'post_title'		=> '',
							'post_excerpt'		=> '',
							'post_author'		=> $post_data[ 'post_author' ],
							'post_content'		=> '',
						);

						//insert the image
						$attach_id = wp_insert_attachment( $attachment, $filedir[ 'path' ] . '/' . $filename );
						if ( ! is_wp_error( $attach_id ) ) {
							wp_update_attachment_metadata(
								$attach_id, wp_generate_attachment_metadata( $attach_id, $filedir[ 'path' ] . '/' . $filename )
							);
							set_post_thumbnail( $remote_post_id, $attach_id );
						} // update the image data
					}
				}
			}
			$this->set_linked_element( $post_id, $blog_id, $remote_post_id );

			restore_current_blog();

		}

		/**
		 * Run after all save_post actions are called in other blogs.
		 *
		 * @param Array $save_context
		 */
		do_action( 'mlp_after_post_synchronization', $this->save_context );
	}

	/**
	 * @param string $post_type
	 * @param int $post_parent
	 * @return void
	 */
	public function find_post_parents( $post_type, $post_parent ) {

		if ( ! is_post_type_hierarchical( $post_type ) )
			return;

		if ( 0 < $post_parent )
			$this->parent_elements = mlp_get_linked_elements( $post_parent );
	}

	/**
	 * @param $blog_id
	 * @return int
	 */
	public function get_post_parent( $blog_id ) {

		if ( empty ( $this->parent_elements ) )
			return 0;

		if ( empty ( $this->parent_elements[ $blog_id ] ) )
			return 0;

		return $this->parent_elements[ $blog_id ];
	}

	/**
	 * Figure out the post ID.
	 *
	 * Inspects POST request data and too, because we get two IDs on auto-drafts.
	 *
	 * @param  int     $post_id
	 * @return int
	 */
	public function get_real_post_id( $post_id ) {

		if ( ! empty ( $this->post_request_data[ 'post_ID' ] ) )
			return (int) $this->post_request_data[ 'post_ID' ];

		return $post_id;
	}

	/**
	 * set the source id of the element
	 *
	 * @param   int $source_content_id   ID of current element
	 * @param   int $remote_site_id      ID of remote site
	 * @param   int $remote_content_id   ID of remote content
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
	 * @return void
	 */
	public function update_remote_post_meta( $remote_post_id, $post_meta = array() ) {

		/**
		 * Filter post meta data before it is saved to the database.
		 *
		 * @param array $post_meta
		 * @param array $context
		 */
		$new_post_meta = apply_filters(
			'mlp_pre_insert_post_meta',
			$post_meta,
			$this->save_context
		);

		if ( empty ( $new_post_meta ) )
			return;

		foreach ( $new_post_meta as $key => $value )
			update_post_meta( $remote_post_id, $key, $value );
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

		$post_meta = array();

		/**
		 * Array of post meta fields to synchronize. Defaults to an empty array.
		 *
		 * @param Array $post_meta
		 * @param Array $save_context
		 */
		return apply_filters(
			'mlp_pre_save_post_meta',
			$post_meta,
			$this->save_context
		);
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

		if ( empty ( $this->post_request_data[ 'post_type' ] ) )
			return $post->post_type;

		if ( 'revision' === $this->post_request_data[ 'post_type' ] )
			return $post->post_type;

		if ( is_string( $this->post_request_data[ 'post_type' ] ) )
			return $this->post_request_data[ 'post_type' ]; // auto-draft

		return $post->post_type;
	}

	/**
	 * @param  string $post_type
	 * @return WP_Post
	 */
	public function get_dummy_post( $post_type ) {

		$dummy            = new stdClass;
		$dummy->post_type = $post_type;
		$dummy->dummy     = TRUE;

		return new WP_Post( $dummy );
	}

	/**
	 * @param  int $blog_id
	 * @return string
	 */
	public function get_remote_language( $blog_id ) {

		static $blogs = FALSE;

		if ( ! $blogs )
			$blogs = get_site_option( 'inpsyde_multilingual' );

		$language = '(' . $blog_id . ')';

		if ( empty ( $blogs[ $blog_id ] ) )
			return $language;

		$data = $blogs[ $blog_id ];

		if ( ! empty ( $data[ 'text' ] ) )
			return $data[ 'text' ];

		if ( ! empty ( $data[ 'lang' ] ) )
			return $data[ 'lang' ];

		return $language;
	}
}