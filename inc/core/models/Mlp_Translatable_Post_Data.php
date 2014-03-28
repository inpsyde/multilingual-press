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
	 * Context for hook on save.
	 *
	 * @var array
	 */
	public $save_context = array ();

	/**
	 * @param Mlp_Request_Validator_Interface $request_validator
	 * @param array $allowed_post_types
	 * @param string $link_table
	 */
	function __construct(
		Mlp_Request_Validator_Interface $request_validator,
		Array                           $allowed_post_types,
	                                    $link_table
	) {
		$this->request_validator  = $request_validator;
		$this->allowed_post_types = $allowed_post_types;
		$this->link_table         = $link_table;
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
			switch_to_blog( $blog_id );
			$post = get_post( $linked[ $blog_id ] );
			restore_current_blog();
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

		if ( ! in_array( $post_type, $this->allowed_post_types ) )
			return;

		if ( empty ( $_POST[ 'mlp_to_translate' ] ) )
			return;

		$this->save_context = array (
			'source_blog'    => get_current_blog_id(),
			'source_post'    => $post,
			'real_post_type' => $post_type
		);

		// Get the post
		$post_data  = get_post( $post_id, ARRAY_A );
		$post_meta = $this->get_post_meta_to_transfer( $post_id );

		// Apply a filter here so modules can play around
		// with the post data before it is processed.
		$post_data = apply_filters( 'mlp_pre_save_postdata', $post_data, $this->save_context );
		$post_meta = apply_filters( 'mlp_pre_save_post_meta', $post_meta, $this->save_context );

		// When the filter returns FALSE, we'll stop here
		if ( FALSE == $post_data || ! is_array( $post_data ) )
			return;

		$this->set_source_id( $post_id );

		$file = $path = '';
		$fileinfo = array ();

		// Check for thumbnail
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			$thumb_id = get_post_thumbnail_id( $post_id );
			if ( 0 < $thumb_id ) {
				$path = wp_upload_dir();
				$file = get_post_meta( $thumb_id, '_wp_attached_file', TRUE );
				$fileinfo = pathinfo( $file );
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


		// Load parent posts
		$parent_elements = array ();

		if ( is_post_type_hierarchical( $post_data[ 'post_type' ] ) && 0 < $post_data[ 'post_parent' ] )
			$parent_elements = mlp_get_linked_elements( $post_data[ 'post_parent' ] );

		// Create a copy of the item for every related blog
		foreach ( $_POST[ 'mlp_to_translate' ] as $blog_id ) {

			if ( $blog_id == get_current_blog_id() or ! blog_exists( $blog_id ) )
				continue;

			switch_to_blog( $blog_id );

			// Set the linked parent post
			if ( ! empty ( $parent_elements ) && ! empty ( $parent_elements[ $blog_id ] ) )
				$new_post[ 'post_parent' ] = $parent_elements[ $blog_id ];

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
				include_once ( ABSPATH . 'wp-admin/includes/image.php' ); //including the attachment function

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
			$this->set_linked_element( $remote_post_id, $post_id );
			restore_current_blog();

		}
	}

	/**
	 * set the source id for starting
	 *
	 * @param   int $sourceid ID of the selected element
	 * @param   int $source_blog ID of the selected blog
	 * @param   string $source_type type of the selected element
	 * @return  void
	 */
	public function set_source_id( $sourceid, $source_blog = 0, $source_type = '' ) {

		$this->source_id = $sourceid;
		if ( 0 == $source_blog )
			$source_blog = get_current_blog_id();

		$this->source_blog_id = $source_blog;
		$this->source_type = $source_type;

		// set the current element as the first linked element
		$this->set_linked_element( $sourceid, $sourceid, $source_blog, $source_type );
	}

	/**
	 * set the source id of the element
	 *
	 * @param   int $element_id ID of the selected element
	 * @param   int $source_id ID of the selected element
	 * @param   int $source_blog_id ID of the selected blog
	 * @param   string $type type of the selected element
	 * @param   int $blog_id ID of the selected blog
	 * @global	$wpdb | WordPress Database Wrapper
	 * @return  void
	 */
	public function set_linked_element( $element_id, $source_id = 0, $source_blog_id = 0, $type = '', $blog_id = 0 ) {

		global $wpdb;

		if ( 0 == $blog_id )
			$blog_id = get_current_blog_id();

		if ( 0 == $source_id )
			$source_id = $this->source_id;

		if ( 0 == $source_blog_id )
			$source_blog_id = $this->source_blog_id;

		$wpdb->insert(
			 $this->link_table,
				 array(
					 'ml_source_blogid'    => $source_blog_id,
					 'ml_source_elementid' => $source_id,
					 'ml_blogid'           => $blog_id,
					 'ml_elementid'        => $element_id,
					 'ml_type'             => $type
				 )
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
	 * @param  int $post_id
	 * @return array
	 */
	public function get_post_meta_to_transfer( $post_id ) {

		$post_meta = get_post_custom( $post_id );

		if ( empty ( $post_meta ) )
			return array();

		// built-in values not to synchronize
		$strip = array (
			'_thumbnail_id' => 1,
			'_edit_last'    => 1,
			'_edit_lock'    => 1,
			'_pingme'       => 1,
			'_encloseme'    => 1
		);

		$diff = array_diff_key( $post_meta, $strip );

		if ( empty ( $diff ) )
			return array();

		$out = array();

		// usually all values are arrays, the last entry is key 0
		foreach ( $diff as $key => $value )
			$out[ $key ] = is_array( $value ) ? $value[0] : $value;

		return $out;
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