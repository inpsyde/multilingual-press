<?php # -*- coding: utf-8 -*-

/**
 * Class Mlp_Translation_Metabox
 *
 *
 *
 * @version 2014.01.15
 * @author  toscho
 * @license GPL
 */
class Mlp_Translation_Metabox {

	/**
	 * @var Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * Used in save_post() to prevent recursion
	 *
	 * @static
	 * @since	0.8
	 * @var		NULL | integer
	 */
	private static $source_blog = NULL;

	/**
	 * source id of the current element
	 *
	 * @static
	 * @since  0.1
	 * @var    string
	 */
	private $source_id = 0;

	/**
	 * source id of the current blog
	 *
	 * @since  0.1
	 * @var    string
	 */
	private $source_blog_id = 0;

	/**
	 * source type of the current content
	 *
	 * @since  0.2
	 * @var    string
	 */
	private $source_type = '';

	/**
	 * @param Inpsyde_Property_List_Interface $data
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->plugin_data = $data;

		// Does another plugin offer its own save method?
		$external_save_method = apply_filters( 'mlp_external_save_method', FALSE );
		if ( ! $external_save_method )
			add_filter( 'save_post', array( $this, 'save_post' ), 10, 2 );

		add_filter( 'wp_ajax_get_metabox_content', array( $this, 'ajax_get_metabox_content' ) );
		// Add the meta box
		$get_metabox_handler = apply_filters( 'mlp_get_metabox_handler', array( $this, 'add_meta_boxes' ) );
		add_filter( 'add_meta_boxes', $get_metabox_handler, 10, 2 );
	}


	/**
	 * add the metaboxes on posts and pages
	 *
	 * @return  void
	 */
	public function add_meta_boxes( $post_type, $post ) {

		// Do we have linked elements?
		$linked = mlp_get_linked_elements( $post->ID );
		if ( ! $linked ) {
			add_meta_box( 'multilingual_press_translate', __( 'Multilingual Press: Translate Post', 'multilingualpress' ), array( $this, 'display_meta_box_translate' ), 'post', 'normal', 'high' );
			add_meta_box( 'multilingual_press_translate', __( 'Multilingual Press: Translate Page', 'multilingualpress' ), array( $this, 'display_meta_box_translate' ), 'page', 'normal', 'high' );
			return;
		}

		// Register metaboxes
		add_meta_box( 'multilingual_press_link', __( 'Multilingual Press: Linked posts', 'multilingualpress' ), array( $this, 'display_meta_box' ), 'post', 'normal', 'high' );
		add_meta_box( 'multilingual_press_link', __( 'Multilingual Press: Linked pages', 'multilingualpress' ), array( $this, 'display_meta_box' ), 'page', 'normal', 'high' );
	}

	/**
	 * show the metabox
	 *
	 * @return  void
	 */
	public function display_meta_box_translate() {

		?>
		<p>
			<label for="translate_this_post">
				<input type="checkbox" id="translate_this_post" name="translate_this_post" <?php
					checked( TRUE, apply_filters( 'mlp_translate_this_post_checkbox', FALSE ) );
				?> />
				<?php _e( 'Translate this post', 'multilingualpress' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * show the metabox
	 *
	 * @param   WP_Post $post post object
	 * @return  void
	 */
	public function display_meta_box( $post ) {

		$linked = mlp_get_linked_elements( $post->ID );
		if ( 0 < count( $linked ) ) { // post is a linked post
			$languages = mlp_get_available_languages();
			if ( 0 < count( $languages ) ) {
				?>
				<select name="multilingual_press" id="multilingual_press">
					<option><?php _e( 'choose preview language', 'multilingualpress' ); ?></option>
				<?php
				foreach ( $languages as $language_blogid => $language_name ) {
					if ( $language_blogid != get_current_blog_id() ) {
						?>
						<option value="<?php echo $language_blogid; ?>"><?php echo $language_name;?></option>
						<?php
					}
				}
				?>
				</select>
				<div id="multilingual_press_content"></div>

				<script type="text/javascript">
					//<![CDATA[
					jQuery( document ).ready( function( $ ) {
						$( '#multilingual_press' ).change( function() {

							blogid = '';
							$( '#multilingual_press option:selected' ).each( function () {
								blogid += $( this ).attr( 'value' );
							} );

							$.post( ajaxurl,
								{
									action: 'get_metabox_content',
									blogid: blogid,
									post: <?php echo $post->ID; ?>
								},
								function( returned_data ) {
									if ( '' != returned_data ) {
										$( '#multilingual_press_content' ).html( returned_data );
									}
								}
							);
						} );
					} );
					//]]>
				</script>
				<?php
			}
		}
	}

	/**
	 * Load the Content for the Metabox
	 *
	 * @access  public
	 * @since   0.1
	 * @uses	switch_to_blog, get_post, get_the_title, get_post_status, admin_url, apply_filters, restore_current_blog
	 * @return  void
	 */
	public function ajax_get_metabox_content() {

		$has_linked = FALSE;

		// Get elements linked to this item
		$linked = mlp_get_linked_elements( esc_attr( $_POST[ 'post' ] ) );

		// No elements available? Au revoir.
		if ( ! $linked )
			die( __( 'No post available', 'multilingualpress' ) );

		// Walk through elements
		foreach ( $linked as $linked_blog => $linked_post ) {

			// Not concerning this blog?
			if ( intval( $_POST[ 'blogid' ] ) !== $linked_blog )
				continue;

			// Switch to appropriate blog to
			// have the helper functions at hand
			switch_to_blog( $linked_blog );

			// Get post
			$remote_post = get_post( $linked_post );

			// Create output
			if ( NULL != $remote_post ) {
				$has_linked = TRUE;
				echo '<p>' . __( 'Status:', 'multilingualpress' ) . '&nbsp;<b>' . ucfirst( get_post_status( $linked_post ) ) . '</b>&nbsp;|&nbsp;' . __( 'Published on:', 'multilingualpress' ) . '<b>&nbsp;' . get_post_time( get_option( 'date_format' ), FALSE, $linked_post ) . '</b></p>';
				echo '<h2 id="headline">' . get_the_title( $linked_post ) . '</h2>';
				echo '<textarea id="content" class="large-text cols="80" rows="10" readonly="readonly">' . apply_filters( 'the_content', $remote_post->post_content ) . '</textarea>';
				echo '<p><a href="' . admin_url( 'post.php?post=' . $linked_post . '&action=edit' ) . '">' . __( 'Edit', 'multilingualpress' ) . '</a></p>';
			}
			restore_current_blog();
		}

		// No posts available?
		if ( FALSE === $has_linked )
			die( '<p>' . __( 'No post available', 'multilingualpress' ) . '</p>' );

		die();
	}

	/**
	 * create the element on other blogs and link them
	 *
	 * @access  public
	 * @since   0.1
	 * @uses	get_post_status, get_post, get_post_thumbnail_id, wp_upload_dir, get_post_meta,
	 *			pathinfo, get_blog_list, get_current_blog_id, switch_to_blog, wp_insert_post,
	 *			wp_unique_filename, wp_check_filetype, is_wp_error, wp_update_attachment_metadata,
	 *			wp_generate_attachment_metadata, update_post_meta, restore_current_blog
	 * @param   int $post_id ID of the post
	 * @param   WP_Post $post object
	 * @return  void
	 */
	public function save_post( $post_id, $post = NULL ) {

		// We're only interested in published posts at this time
		if ( ! in_array( $post->post_status, array( 'publish', 'draft', 'private' ) ) )
			return;

		// Avoid recursion:
		// wp_insert_post() invokes the save_post hook, so we have to make sure
		// the loop below is only entered once per save action. Therefore we save
		// the source_blog in a static class variable. If it is already set we
		// know the loop has already been entered and we can exit the save action.
		if ( NULL === self::$source_blog )
			self::$source_blog = get_current_blog_id();
		else
			return;

		// If checkbox is not checked, return
		if ( ! isset( $_POST[ 'translate_this_post' ] ) )
			return;

		// Get the post
		$postdata  = get_post( $post_id, ARRAY_A );
		$post_meta = $this->get_post_meta_to_transfer( $post_id );

		// Apply a filter here so modules can play around
		// with the postdata before it is processed.
		$postdata  = apply_filters( 'mlp_pre_save_postdata',  $postdata );
		$post_meta = apply_filters( 'mlp_pre_save_post_meta', $post_meta );

		// If there is no filter hooked into this saving method, then we
		// will exclude all post types other that "post" and "page".
		// @TODO: improve this logic :/
		// @TODO: create a whitelist for allowed post types, incl. apply_filters() ?
		if ( ! has_filter( 'mlp_pre_save_postdata' ) ) {
			if ( 'post' != $postdata[ 'post_type'] && 'page' != $postdata[ 'post_type'] )
				return;
		}

		// When the filter returns FALSE, we'll stop here
		if ( FALSE == $postdata || ! is_array( $postdata ) )
			die( __LINE__ );

		$linked = mlp_get_linked_elements( $post_id );

		// We already linked this element?
		if ( 0 !== count( $linked ) )
			return;

		$this->set_source_id( $post_id );
		$file = '';

		// Check for thumbnail
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			$thumb_id = get_post_thumbnail_id( $post_id );
			if ( 0 < $thumb_id ) {
				$path = wp_upload_dir();
				$file = get_post_meta( $thumb_id, '_wp_attached_file', true );
				$fileinfo = pathinfo( $file );
			}
		}

		// Create the post array
		$newpost = array(
			'post_title'	=> $postdata[ 'post_title' ],
			'post_content'	=> $postdata[ 'post_content' ],
			'post_status'	=> 'draft',
			'post_author'	=> $postdata[ 'post_author' ],
			'post_excerpt'	=> $postdata[ 'post_excerpt' ],
			'post_date'		=> $postdata[ 'post_date' ],
			'post_type'		=> $postdata[ 'post_type' ]
		);

		$blogs = mlp_get_available_languages();

		if ( empty( $blogs ) )
			return;

		// Load Page Parents
		$parent_elements = array( );
		if ( 'page' == $postdata[ 'post_type' ] && 0 < $postdata[ 'post_parent' ] )
			$parent_elements = mlp_get_linked_elements( $postdata[ 'post_parent' ] );

		// Create a copy of the item for every related blog
		foreach ( $blogs as $blogid => $blogname ) {

			if ( $blogid != self::$source_blog ) {

				switch_to_blog( $blogid );

				// Set the linked parent page
				if ( 0  < count( $parent_elements ) && 0 < $parent_elements[ $blogid ] )
					$newpost[ 'post_parent'] = $parent_elements[ $blogid ];

				// use filter to change postdata for every blog
				$newpost = apply_filters( 'mlp_pre_insert_post', $newpost );

				// Insert remote blog post
				$remote_post_id = wp_insert_post( $newpost );

				if ( ! empty ( $post_meta ) )
					$this->update_remote_post_meta( $remote_post_id, $post_meta );

				if ( '' != $file ) { // thumbfile exists
					include_once ( ABSPATH . 'wp-admin/includes/image.php' ); //including the attachment function
					if ( 0 < count( $fileinfo ) ) {

						$filedir = wp_upload_dir();
						$filename = wp_unique_filename( $filedir[ 'path' ], $fileinfo[ 'basename' ] );
						$copy = copy( $path[ 'basedir' ] . '/' . $file, $filedir[ 'path' ] . '/' . $filename );

						if ( $copy ) {
							unset( $postdata[ 'ID' ] );
							$wp_filetype = wp_check_filetype( $filedir[ 'url' ] . '/' . $filename ); //get the file type
							$attachment = array(
								'post_mime_type'	=> $wp_filetype[ 'type' ],
								'guid'				=> $filedir[ 'url' ] . '/' . $filename,
								'post_parent'		=> $remote_post_id,
								'post_title'		=> '',
								'post_excerpt'		=> '',
								'post_author'		=> $postdata[ 'post_author' ],
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
				$this->set_linked_element( $remote_post_id );
				restore_current_blog();
			}
		}
	}

	/**
	 * Check source post for meta data to transfer.
	 *
	 * Called in save_post().
	 *
	 * @since  1.0.4
	 * @param  int   $source_post_id
	 * @return array
	 */
	protected function get_post_meta_to_transfer( $source_post_id ) {

		$post_meta = get_post_custom( $source_post_id );

		if ( empty( $post_meta ) )
			return array();

		// built-in values not to synchronize
		$strip = array (
			'_thumbnail_id',
			'_edit_last',
			'_edit_lock',
			'_pingme',
			'_encloseme'
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
	 * set the source id for starting
	 *
	 * @access  public
	 * @since   0.1
	 * @uses	get_current_blog_id
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
	 * Add source post meta to remote post.
	 *
	 * Called in save_post().
	 *
	 * @since  1.0.4
	 * @param  int   $remote_post_id
	 * @param  array $post_meta
	 * @return void
	 */
	protected function update_remote_post_meta( $remote_post_id, $post_meta = array() ) {

		if ( empty( $post_meta ) )
			return;

		foreach ( $post_meta as $key => $value )
			update_post_meta( $remote_post_id, $key, $value );
	}

	/**
	 * set the source id of the element
	 *
	 * @access  public
	 * @since   0.1
	 * @uses	get_current_blog_id
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
			$this->plugin_data->link_table,
			array(
				'ml_source_blogid'    => $source_blog_id,
				'ml_source_elementid' => $source_id,
				'ml_blogid'           => $blog_id,
				'ml_elementid'        => $element_id,
				'ml_type'             => $type
			)
		);
	}
}