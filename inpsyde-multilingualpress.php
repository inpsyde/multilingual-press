<?php
/**
 * Plugin Name: Multilingual Press
 * Plugin URI:  https://github.com/inpsyde/multilingual-press
 * Description: By using the WordPress plugin Multilingual-Press it's much easier to build multilingual sites and run them with WordPress Multisite feature. 
 * Author:	  Inpsyde GmbH
 * Author URI:  http://inpsyde.com
 * Version:	 0.7a (Alpha)
 * Text Domain: inpsyde_multilingualpress
 * Domain Path: /languages
 * License: GPLv3
 */
if ( ! class_exists( 'Inpsyde_Multilingualpress' ) ) {

	class Inpsyde_Multilingualpress {

		/**
		 * The class object
		 *
		 * @static
		 * @since  0.1
		 * @var    string
		 */
		static protected $class_object = NULL;

		/**
		 * The linked elements table
		 *
		 * @since  0.1
		 * @var    string
		 */
		protected $link_table = '';
		
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
		 * Array containing loaded modules
		 *
		 * @since  0.5
		 * @var    array
		 */
		protected $registered_modules = FALSE;
		
		/**
		 * array containing language codes and names
		 *
		 * @protected
		 * @since  0.5
		 * @var    array
		 */
		protected $lang_codes; // 

		/**
		 * to load the object and get the current state 
		 *
		 * @access  public
		 * @since   0.1
		 * @return  $class_object
		 */

		function get_object() {

			if ( NULL == self::$class_object ) {
				self::$class_object = new self;
			}
			return self::$class_object;
		}

		/**
		 * init function to register all used hooks,
		 * load class files and set parameters
		 * such as the database table 
		 *
		 * @access  public
		 * @since   0.1
		 * @uses	add_action, get_site_option
		 * @return  void
		 */
		function __construct() {

			global $wpdb;

			$wpdb->show_errors();

			// Set linked elements table
			$this->link_table = $wpdb->base_prefix . 'multilingual_linked';

			// Load classes
			$this->include_files();

			do_action( 'inpsyde_mlp_init' );

			// Hooks and filters
			add_action( 'init', array( $this, 'localize_plugin' ) );
			add_action( 'admin_init', array( $this, 'get_lang_codes' ), 1 );

			add_action( 'plugins_loaded', array( $this, 'load_modules' ), 9 );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			// Does another plugin offer its own save method?
			$external_save_method = apply_filters( 'inpsyde_multilingualpress_external_save_method', FALSE );
			if ( ! $external_save_method )
				add_action( 'save_post', array( $this, 'save_post' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_styles' ) );

			add_action( 'wp_ajax_tab_form', array( $this, 'draw_blog_settings_form' ) );
			add_action( 'wp_ajax_save_multilang_settings', array( $this, 'update_blog_settings' ) );
			add_action( 'wp_ajax_get_metabox_content', array( $this, 'ajax_get_metabox_content' ) );

			add_filter( 'contextual_help', array( $this, 'context_help' ), 10, 3 );

			add_action( 'delete_blog', array( $this, 'delete_blog' ), 10, 2 );

			// Form / save hooks also used by modules
			add_action( 'mlp_blogs_add_fields', array( $this, 'blogs_form_fields' ) );
			add_action( 'mlp_blogs_save_fields', array( $this, 'blogs_save_fields' ) );
		}

		private function include_files() {

			global $pagenow;

			// Include helper functions
			require_once( 'inc/class-mlp-helpers.php' );
			// Include widget
			require_once( 'inc/class-mlp-widget.php' );
			
			// Page specific admin files
			$hook = array( 'sites.php' );
			if ( is_admin() && in_array( $pagenow, $hook ) ) {

				require_once( 'inc/class-mlp-custom-columns.php' );
				add_action( 'init', array( 'mlp_custom_columns', 'init' ) );
			}

			// Global admin files
			if ( is_admin() ) {

				// Include settings page _after_ modules are loaded
				require_once( 'inc/class-mlp-settings-page.php' );
				add_action( 'plugins_loaded', array( 'inpsyde_multilingualpress_settingspage', 'get_object' ), 10 );
			}
		}

		/**
		 * Load frontend CSS
		 * 
		 * @since  0.5.3b
		 */
		public function wp_styles() {

			wp_enqueue_style( 'mlp-frontend-css', plugins_url( 'css/frontend.css', __FILE__ ) );
		}

		/**
		 * Load admin javascript and CSS
		 *
		 * @since 1.0
		 */
		public function admin_scripts() {

			global $pagenow;

			$pages = array( 'site-info.php', 'site-users.php', 'site-themes.php', 'site-settings.php' );

			if ( in_array( $pagenow, $pages ) ) {

				wp_enqueue_script( 'mlp-js', plugins_url( 'js/', __FILE__ ) . 'multilingualpress.js' );
				wp_localize_script( 'mlp-js', 'mlp_loc', $this->localize_script() );
				wp_enqueue_style( 'mlp-admin-css', plugins_url( 'css/admin.css', __FILE__ ) );
			}
		}

		/**
		 * Make localized strings available in javascript
		 *
		 * @return type $loc | Array containing localized strings
		 */
		public function localize_script() {

			$loc = array(
				'tab_label'                  => __( 'Multilingual Press', $this->get_textdomain() ),
				'blog_id'                    => intval( $_GET[ 'id' ] ),
				'ajax_tab_nonce'             => wp_create_nonce( 'mlp_tab_nonce' ),
				'ajax_form_nonce'            => wp_create_nonce( 'mlp_form_nonce' ),
				'ajax_select_nonce'          => wp_create_nonce( 'mlp_select_nonce' ),
				'ajax_switch_language_nonce' => wp_create_nonce( 'mlp_switch_language_nonce' )
			);
			return $loc;
		}

		/**
		 * Return Textdomain string
		 *
		 * @access public
		 * @since 0.1
		 * @return string
		 */
		public function get_textdomain() {

			return 'inpsyde_multilingualpress';
		}

		/**
		 * register the textdomain
		 *
		 * @access  public
		 * @since   0.1
		 * @uses	load_plugin_textdomain, plugin_basename
		 * @return  void
		 */
		public function localize_plugin() {

			load_plugin_textdomain(
				$this->get_textdomain(),
				FALSE,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);
		}

		/**
		 * function to get the element ID in other blogs for the selected element 
		 *
		 * @access  public
		 * @since   0.1
		 * @uses	get_current_blog_id, get_results, get_results
		 * @param   int $element_id ID of the selected element
		 * @param   string $type type of the selected element
		 * @param   int $blog_id ID of the selected blog
		 * @return  array $elements
		 */
		function load_linked_elements( $element_id, $type = '', $blog_id = 0 ) {

			global $wpdb;

			if ( 0 == $blog_id ) {
				$blog_id = get_current_blog_id();
			}
			// Wieso %s und nicht %d?
			$results = $wpdb->get_results( $wpdb->prepare( 'SELECT t.ml_blogid, t.ml_elementid FROM ' . $this->link_table . ' s INNER JOIN ' . $this->link_table . ' t ON s.ml_source_blogid = t.ml_source_blogid && s.ml_source_elementid = t.ml_source_elementid WHERE s.ml_blogid = %s && s.ml_elementid = %s', $blog_id, $element_id ) );
			$elements = array( );
			if ( 0 < count( $results ) ) {
				foreach ( $results as $resultelement ) {
					if ( $blog_id != $resultelement->ml_blogid ) {
						$elements[ $resultelement->ml_blogid ] = $resultelement->ml_elementid;
					}
				}
			}
			return $elements;
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
		function set_source_id( $sourceid, $source_blog = 0, $source_type = '' ) {

			$this->source_id = $sourceid;
			if ( 0 == $source_blog ) {
				$source_blog = get_current_blog_id();
			}
			$this->source_blog_id = $source_blog;
			$this->source_type = $source_type;
			// set the current element as the first linked element
			$this->set_linked_element( $sourceid, $sourceid, $source_blog, $source_type );
		}

		/**
		 * set the source id of the element
		 *
		 * @access  public
		 * @since   0.1
		 * @uses	get_current_blog_id
		 * @param   int $element_id ID of the selected element
		 * @param   int $sourceid ID of the selected element
		 * @param   int $source_blog ID of the selected blog
		 * @param   string $type type of the selected element
		 * @param   int $blog_id ID of the selected blog
		 * @return  void
		 */
		function set_linked_element( $element_id, $source_id = 0, $source_blog_id = 0, $type = '', $blog_id = 0 ) {

			global $wpdb;

			if ( 0 == $blog_id ) {
				$blog_id = get_current_blog_id();
			}
			if ( 0 == $source_id ) {
				$source_id = $this->source_id;
			}
			if ( 0 == $source_blog_id ) {
				$source_blog_id = $this->source_blog_id;
			}
			$wpdb->insert( $this->link_table, array( 'ml_source_blogid' => $source_blog_id, 'ml_source_elementid' => $source_id, 'ml_blogid' => $blog_id, 'ml_elementid' => $element_id, 'ml_type' => $type ) );
		}

		/**
		 * create the element on other blogs and link them
		 *
		 * @access  public
		 * @since   0.1
		 * @uses    get_post_status, get_post, get_post_thumbnail_id, wp_upload_dir, get_post_meta, 
		 *          pathinfo, get_blog_list, get_current_blog_id, switch_to_blog, wp_insert_post, 
		 *          wp_unique_filename, wp_check_filetype, is_wp_error, wp_update_attachment_metadata, 
		 *          wp_generate_attachment_metadata, update_post_meta, restore_current_blog
		 * @param   $post_id ID of the post
		 * @return  void
		 */
		function save_post( $post_id ) {

			$post_status = get_post_status( $post_id );

			if ( 'publish' !== $post_status )
				return;

			$linked = $this->load_linked_elements( $post_id );

			if ( 0 !== count( $linked ) )
				return;

			$this->set_source_id( $post_id );
			$postdata = get_post( $post_id, ARRAY_A );
			$file = '';
			if ( current_theme_supports( 'post-thumbnails' ) ) {
				$thumb_id = get_post_thumbnail_id( $post_id );
				if ( 0 < $thumb_id ) {
					$path = wp_upload_dir();
					$file = get_post_meta( $thumb_id, '_wp_attached_file', true );
					$fileinfo = pathinfo( $file );
				}
			}
			$newpost = array(
				'post_title' => $postdata[ 'post_title' ],
				'post_content' => $postdata[ 'post_content' ],
				'post_status' => 'draft',
				'post_author' => $postdata[ 'post_author' ],
				'post_excerpt' => $postdata[ 'post_excerpt' ],
				'post_date' => $postdata[ 'post_date' ],
				'post_type' => $postdata[ 'post_type' ]
			);
			$blogs = $this->get_available_languages();

			$current_blog = get_current_blog_id();

			if ( ! ( 0 < count( $blogs ) ) )
				return;

			// Create a copy of the item for every related blog
			foreach ( $blogs as $blogid => $blogname ) {

				if ( $blogid != $current_blog ) {
					switch_to_blog( $blogid );

					// Insert remote blog post
					$remote_post_id = wp_insert_post( $newpost );
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
									'post_mime_type' => $wp_filetype[ 'type' ],
									'guid' => $filedir[ 'url' ] . '/' . $filename,
									'post_parent' => $remote_post_id,
									'post_title' => '',
									'post_excerpt' => '',
									'post_author' => $postdata[ 'post_author' ],
									'post_content' => '',
								);
								//insert the image
								$attach_id = wp_insert_attachment( $attachment, $filedir[ 'path' ] . '/' . $filename );
								if ( !is_wp_error( $attach_id ) ) {
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
		 * add the metaboxes on posts and pages
		 *
		 * @access  public
		 * @since   0.1
		 * @uses	add_meta_box
		 * @return  void
		 */
		function add_meta_boxes() {

			global $post;

			$linked = $this->load_linked_elements( $post->ID );
			if ( ! $linked )
				return;

			add_meta_box( 
				'inpsyde_multilingualpress_link', 
				__( 'Linked posts', $this->get_textdomain() ), 
				array( $this, 'display_meta_box' ), 
				'post', 'normal', 'high'
			);
			add_meta_box( 
				'inpsyde_multilingualpress_link', 
				__( 'Linked pages', $this->get_textdomain() ), 
				array( $this, 'display_meta_box' ), 
				'page', 'normal', 'high' 
			);
		}

		/**
		 * show the metabox
		 *
		 * @access  public
		 * @since   0.1
		 * @uses    get_site_option, get_current_blog_id
		 * @param   $post post object  
		 * @return  void
		 */
		function display_meta_box( $post ) {

			$linked = $this->load_linked_elements( $post->ID );
			if ( 0 < count( $linked ) ) { // post is a linked post
				$languages = $this->get_available_languages();
				if ( 0 < count( $languages ) ) {
					echo '<select name="inpsyde_multilingual" id="inpsyde_multilingual"><option>' . __( 'choose preview language', $this->get_textdomain() ) . '</option>';
					foreach ( $languages as $language_blogid => $language_name ) {
						if ( $language_blogid != get_current_blog_id() ) {
							echo '<option value="' . $language_blogid . '">' . $language_name . '</option>';
						}
					}
					echo '</select><div id="inpsyde_multilingual_content"></div>';
					echo '<script type="text/javascript">
					//<![CDATA[
					jQuery(document).ready(function($) {
						$("#inpsyde_multilingual").change(function() {
							blogid = "";
							$("#inpsyde_multilingual option:selected").each(function () {
								blogid += $(this).attr( "value" );
							});
							$.post( ajaxurl, { action: "get_metabox_content", blogid:blogid, post: ' . $post->ID . ' },
								function( returned_data ) {
									if ( "" != returned_data ) {
										$( "#inpsyde_multilingual_content").html( returned_data );
									}
								}
							);
						});
					});
					//]]>
					</script>';
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
		function ajax_get_metabox_content() {

			$has_linked = FALSE;

			// Get elements linked to this item
			$linked = $this->load_linked_elements( esc_attr( $_POST[ 'post' ] ) );

			if ( !$linked )
				die( __( 'No post available', $this->get_textdomain() ) );

			foreach ( $linked as $linked_blog => $linked_post ) {
				if ( $_POST[ 'blogid' ] == $linked_blog ) {

					switch_to_blog( $linked_blog );
					$remote_post = get_post( $linked_post );

					if ( NULL != $remote_post ) {
						$has_linked = TRUE;
						echo '<p>' . __( 'Status:', $this->get_textdomain() ) . '&nbsp;<b>' . ucfirst( get_post_status( $linked_post ) ) . '</b>&nbsp;|&nbsp;' . __( 'Published on:', $this->get_textdomain() ) . '<b>&nbsp;' . get_post_time( get_option( 'date_format' ), FALSE, $linked_post ) . '</b></p>';
						echo '<h2>' . get_the_title( $linked_post ) . '</h2>';
						echo '<textarea class="large-text cols="80" rows="10" disabled="disabled">' . apply_filters( 'the_content', $remote_post->post_content ) . '</textarea><br />';
						echo '<p><a href="' . admin_url( 'post.php?post=' . $linked_post . '&action=edit' ) . '">' . __( 'Edit', $this->get_textdomain() ) . '</a></p>';
					}
					restore_current_blog();
				}
			}

			if ( FALSE === $has_linked )
				die( '<p>' . __( 'No post available', $this->get_textdomain() ) . '</p>' );

			die();
		}

		/**
		 * Display the options on the network admin blog options 
		 *
		 * @access  public
		 * @since   0.1
		 * @uses	get_site_option, scandir, dirname, plugins_url, get_site_option
		 * @param   $id ID of the blog
		 * @return  void
		 */
		function draw_blog_settings_form() {

			// check nonce
			check_ajax_referer( 'mlp_tab_nonce', 'tab_nonce' );

			// Get blog id
			$current_blog_id = isset( $_REQUEST[ 'id' ] ) ? intval( $_REQUEST[ 'id' ] ) : 0;

			if ( ! $current_blog_id )
				wp_die( __( 'Invalid site ID.' ) );

			$site_url_no_http = preg_replace( '#^http(s)?://#', '', get_blogaddress_by_id( $current_blog_id ) );
			$title_site_url_linked = sprintf( __( 'Edit Site: <a href="%1$s">%2$s</a>' ), get_blogaddress_by_id( $current_blog_id ), $site_url_no_http );
			?>

			<?php screen_icon( 'ms-admin' ); ?>

			<h2 id="edit-site"><?php echo $title_site_url_linked ?></h2>
			<h3 class="nav-tab-wrapper">
				<a class="nav-tab" href="site-info.php?id=<?php echo $current_blog_id; ?>"><?php _e( 'Info' ); ?></a><a class="nav-tab" href="site-users.php?id=<?php echo $current_blog_id; ?>"><?php _e( 'Users' ); ?></a><a class="nav-tab" href="site-themes.php?id=<?php echo $current_blog_id; ?>"><?php _e( 'Themes' ); ?></a><a class="nav-tab" href="site-settings.php?id=<?php echo $current_blog_id; ?>"><?php _e( 'Settings' ); ?></a><a href="#" class="nav-tab nav-tab-active" id="mlp_settings_tab"><?php _e( 'Multilingual Press', $this->get_textdomain() ); ?></a>
			</h3>

			<form action="" method="post" id="multilingualpress_settings">

				<!-- Modules can hook their form fields here -->

			<?php do_action( 'mlp_blogs_add_fields', $current_blog_id ); ?>


				<!-- Secondary fields hook -->

			<?php do_action( 'mlp_blogs_add_fields_secondary', $current_blog_id ); ?>

			<?php submit_button(); ?>

			</form>


			<?php
			die;
		}

		/**
		 * Make available a hook to save blog settings
		 * @TODO: how to pass modules' POST parameters via AJAX
		 *
		 * @access  public
		 * @since   0.1
		 * @uses    get_site_option, update_site_option, delete_site_option
		 * @return  void
		 */
		public function update_blog_settings() {

			// check nonce
			check_ajax_referer( 'mlp_form_nonce', 'form_nonce' );

			// Modules can hook their saving routine here (not implemented yet)
			do_action( 'mlp_blogs_save_fields', $_POST );
			?>

			<div class="updated"><p><?php _e( 'Blog settings saved', $this->get_textdomain() ); ?></p></div>

			<?php
			die;
		}

		/**
		 * Display the default form fields
		 * 
		 * @param   type $current_blog_id | The ID of the current blog
		 * @return  type 
		 * @since   0.5.5b
		 */
		public function blogs_form_fields( $current_blog_id ) {

			// get site options
			$siteoption = get_site_option( 'inpsyde_multilingual' );
			$lang_title = ISSET( $siteoption[ $current_blog_id ][ 'text' ] ) ? stripslashes( $siteoption[ $current_blog_id ][ 'text' ] ) : '';
			$selected = ISSET( $siteoption[ $current_blog_id ][ 'lang' ] ) ? $siteoption[ $current_blog_id ][ 'lang' ] : '';

			// get blog optioins
			$blogoption_flag = esc_url( get_blog_option( $current_blog_id, 'inpsyde_multilingual_flag_url' ) );
			?>

			<!-- Language select, blog description and flag image url (optional) -->

			<div class="postbox">
				<div title="Click to toggle" class="handlediv"><br></div>
				<h3 class="hndle"><?php _e( 'Language', $this->get_textdomain() ); ?></h3>
				<div class="inside">

					<table class="form-table" id="mlp_blog_language">
						<tr class="form-field">
							<th><?php _e( 'Choose blog language', $this->get_textdomain() ) ?></th>
							<td>
			<?php
			if ( !empty( $this->lang_codes ) ) {
				?>
									<select name="inpsyde_multilingual_lang" id="inpsyde_multilingual_lang">
										<option value="-1"><?php _e( 'choose language', $this->get_textdomain() ); ?></option>
										<option  style="background-position:0px 50%;background-image:url(<?php echo plugins_url( 'flags/us.gif', __FILE__ ); ?>);background-repeat:no-repeat;padding-left:30px;" value="en" <?php echo selected( 'en', $selected ); ?>><?php _e( 'English', $this->get_textdomain() ) ?></option>

				<?php foreach ( $this->lang_codes as $language_code => $language_name ) :
					if ( file_exists( plugin_dir_path( __FILE__ ) . '/flags/' . $language_code . '.gif' ) ) : ?>

												<option style="background-position:0px 50%; background-image:url(<?php echo plugins_url( 'flags/' . $language_code . '.gif', __FILE__ ); ?>); background-repeat:no-repeat; padding-left:30px;" value="<?php echo $language_code ?>"<?php echo selected( $selected, $language_code, false ); ?>><?php echo esc_html( $language_name ); ?></option>

												<?php
											endif;
										endforeach;
										?>
									</select>
									<br />

									<span class="description"><?php _e( 'Determine blog language and flag. This will be used in the frontend widget.', $this->get_textdomain() ); ?></span>

			<?php } ?>
							</td>
						</tr>
						<tr>
							<th>
			<?php _e( 'Title', $this->get_textdomain() ); ?>
							</th>
							<td>
								<input class="regular-text" type="text" id="inpsyde_multilingual_text" name="inpsyde_multilingual_text" value="<?php echo $lang_title; ?>" />
							</td>
						</tr>
						<tr>
							<th>
			<?php _e( 'Blog flag image URL', $this->get_textdomain() ); ?>
							</th>
							<td>
								<input class="regular-text" type="text" id="inpsyde_multilingual_flag_url" name="inpsyde_multilingual_flag_url" value="<?php echo $blogoption_flag; ?>" />
								<br />
								<span class="description"><?php _e( '(optional, must begin with http://)', $this->get_textdomain() ); ?></span>
							</td>
						</tr>
					</table>

					<table id="mlp_check_language" style="display:none"><tbody><tr><td></td></tr></table>

				</div>
			</div>

			<?php
			// Only display this part if there are blogs to interlink
			if ( 1 == count( $this->get_available_languages( FALSE ) ) )
				return;
			?>


			<!-- Blog relationships -->		

			<div class="postbox">
				<div title="Click to toggle" class="handlediv"><br></div>
				<h3 class="hndle"><?php _e( 'Blog Relationships', $this->get_textdomain() ); ?></h3>
				<div class="inside">
					<table class="form-table">
						<tr>
							<th><?php _e( 'Multilingual Blog Relationship', $this->get_textdomain() ) ?></th>
							<td>
								<?php
								foreach ( $siteoption as $blog_id => $meta ) {

									// Filter out current blog
									if ( $current_blog_id === $blog_id )
										continue;

									// Get blog display name
									switch_to_blog( $blog_id );
									$blog_name = get_bloginfo( 'name' );
									restore_current_blog();

									// Get current settings
									$related_blogs = get_blog_option( $current_blog_id, 'inpsyde_multilingual_blog_relationship' );
									if ( is_array( $related_blogs ) && in_array( $blog_id, $related_blogs ) )
										$selected = 'checked="checked"';
									else
										$selected = '';
									?>
									<input id="related_blog" <?php echo $selected; ?> type="checkbox" name="related_blogs[]" value="<?php echo $blog_id ?>" /> <?php echo $blog_name; ?> - <?php echo $meta[ 'lang' ] ?> - <?php echo $meta[ 'text' ] ?><br />
				<?php
			}
			?>
								<span class="description"><?php _e( 'Posts and pages will be automaticaly duplicated into these blogs', $this->get_textdomain() ); ?></span>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php
		}

		/**
		 * Process the default form fields
		 *  
		 * @param   array $data | User input
		 * @since   0.5.5b
		 */
		public function blogs_save_fields( $data ) {

			$current_blog_id = intval( $data[ 'id' ] );

			// Language and descriptions
			$siteoption = get_site_option( 'inpsyde_multilingual' );
			unset( $siteoption[ $current_blog_id ] );
			if ( '' != $data[ 'inpsyde_multilingual_lang' ] || '' != $data[ 'inpsyde_multilingual' ] ) {

				if ( !is_array( $siteoption ) )
					$siteoption = array( );

				$siteoption[ $current_blog_id ] = array( 'text' => esc_attr( $data[ 'inpsyde_multilingual_text' ] ), 'lang' => esc_attr( $data[ 'inpsyde_multilingual_lang' ] ) );
			}
			update_site_option( 'inpsyde_multilingual', $siteoption );

			// Custom flag URL
			if ( ISSET( $data[ 'inpsyde_multilingual_flag_url' ] ) )
				update_blog_option( $current_blog_id, 'inpsyde_multilingual_flag_url', esc_url( $data[ 'inpsyde_multilingual_flag_url' ], array( 'http' ) ) );

			// Update blog relationships
			// Get blogs related to the current blog
			$all_blogs = get_site_option( 'inpsyde_multilingual' );

			//p( $all_blogs, "ALL BLOGS OF NETWORK" );

			if ( ! $all_blogs )
				$all_blogs = array( );

			// The user defined new relationships for this blog. We add it's own ID 
			// for internal purposes
			$data[ 'related_blogs' ][ ] = $current_blog_id;
			$new_rel = $data[ 'related_blogs' ];

			//p( $new_rel, "NEW RELATIONSHIPS SET BY USER" );
			// Unchanged settings? Leave out the loop below, then.
			// Loop through related blogs
			foreach ( $all_blogs as $blog_id => $blog_data ) {

				if ( $current_blog_id == $blog_id )
					continue;

				// 1. Get related blogs' current relationships 
				$current_rel = get_blog_option( $blog_id, 'inpsyde_multilingual_blog_relationship' );

				if ( ! is_array( $current_rel ) )
					$current_rel = array( );

				// 2. Compare old to new relationships
				// Get the key of the current blog in the relationships array of the looped blog
				$key = array_search( $current_blog_id, $current_rel );

				if ( in_array( $blog_id, $new_rel ) ) {
					//p( $key, "KEY" );
					// Connect these blogs, if not already.
					if ( FALSE === $key ) {
						$current_rel[ ] = $current_blog_id;
					}
				} else {
					// These blogs should not be connected. Delete
					// possibly existing connection
					if ( FALSE !== $key && ISSET( $current_rel[ $key ] ) )
						unset( $current_rel[ $key ] );
				}

				// $current_rel should be our relationships array for the currently looped blog
				//p( $current_rel, "RELATED BLOGS OF REMOTE BLOG" );
				update_blog_option( $blog_id, 'inpsyde_multilingual_blog_relationship', $current_rel );
			}

			// Save Blog-Relationship
			// @TODO: validate user input
			update_blog_option( $current_blog_id, 'inpsyde_multilingual_blog_relationship', $new_rel );

			// Do not pass on these values 
			$unset = array( 'inpsyde_multilingual', 'inpsyde_multilingual_text', 'inpsyde_multilingual_lang', 'inpsyde_multilingual_flag_url', 'related_blogs' );
			foreach ( $unset AS $del ) {
				unset( $data[ $del ] );
			}

			// Put data back into superglobale
			// for passing it on to modules
			$_POST = $data;
		}

		/**
		 * Load the languages set for each blog
		 *
		 * @access  public
		 * @since   0.1
		 * @uses    get_site_option,get_blog_option, get_current_blog_id, format_code_lang
		 * @param   $rel | filter out related blogs? By default
		 * @return  array $options
		 */
		function get_available_languages( $filter = TRUE ) {

			$languages = get_site_option( 'inpsyde_multilingual' );

			if ( TRUE === $filter )
				$related_blogs = get_blog_option( get_current_blog_id(), 'inpsyde_multilingual_blog_relationship' );

			$options = array( );
			foreach ( $languages as $language_blogid => $language_data ) {

				// Filter out blogs that are not related
				if ( ISSET( $related_blogs ) 
					 && is_array( $related_blogs ) 
					 && !in_array( $language_blogid, $related_blogs ) 
					 && TRUE === $filter
					)
					continue;

				$lang = $language_data[ 'lang' ];
				// We only need the first two letters 
				// of the language code
				if ( 2 !== strlen( $lang ) ) {

					$lang = substr( $lang, 0, 2 );
					if ( is_admin() ) {
						$lang = format_code_lang( $lang );
					}
				}
				$options[ $language_blogid ] = $lang;
			}
			return $options;
		}

		/**
		 * Load the title set for each blog 
		 * @TODO: != title
		 *
		 * @access  public
		 * @since   0.5.3b
		 * @uses    get_site_option
		 * @return  array $options
		 */
		function get_available_languages_titles( $filter = TRUE ) {

			$languages = get_site_option( 'inpsyde_multilingual' );

			if ( TRUE === $filter )
				$related_blogs = get_blog_option( get_current_blog_id(), 'inpsyde_multilingual_blog_relationship' );

			if ( !is_array( $related_blogs ) && TRUE === $filter )
				return;

			$options = array( );
			foreach ( $languages as $language_blogid => $language_data ) {

				// Filter out blogs that are not related
				if ( is_array( $related_blogs ) && !in_array( $language_blogid, $related_blogs ) && TRUE === $filter )
					continue;

				$lang = $language_data[ 'text' ];
				if ( '' == $lang ) {
					$lang = substr( $language_data[ 'lang' ], 0, 2 ); // get the first lang element
					if ( is_admin() ) {
						$lang = format_code_lang( $lang );
					}
				}
				$options[ $language_blogid ] = $lang;
			}
			return $options;
		}

		/**
		 * save all language codes from wordpress  
		 *
		 * @access  public
		 * @param   array $lang_codes | languages from wordpress
		 * @since   0.1
		 * @return  array $lang_codes
		 */
		function load_lang_codes( $lang_codes ) {

			$this->lang_codes = $lang_codes;

			return $lang_codes;
		}

		/**
		 * add filter to get the language 
		 * shortcodes from wordpress  
		 *
		 * @access  public
		 * @since   0.1
		 * @return  array $lang_codes
		 */
		function get_lang_codes() {

			// Get Current Language Codes
			add_filter( 'lang_codes', array( $this, 'load_lang_codes' ) );
			format_code_lang( '' ); // hack to get all available languages
			remove_filter( 'lang_codes', array( $this, 'load_lang_codes' ) );
		}

		/**
		 * create the element links database table  
		 *
		 * @access  public
		 * @since   0.1
		 * @uses    dbDelta
		 * @return  void
		 */
		function install_plugin() {

			global $wpdb;

			$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->base_prefix . 'multilingual_linked (
						`ml_id` INT NOT NULL AUTO_INCREMENT ,
						`ml_source_blogid` int(11) NOT NULL,
						`ml_source_elementid` int(11) NOT NULL,
						`ml_blogid` INT NOT NULL,
						`ml_elementid` INT NOT NULL,
						`ml_type` varchar(20) CHARACTER SET utf8 NOT NULL,
						PRIMARY KEY ( `ml_id` ) , INDEX ( `ml_blogid` , `ml_elementid` )
					);';
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		/**
		 * Returns array of modules
		 *
		 * @access  private
		 * @since   0.1
		 * @return  array Files to include
		 */
		function load_modules() {

			$this->registered_modules = array( );

			if ( ! $dh = @opendir( dirname( __FILE__ ) . '/features' ) ) {
				return;
			}
			while ( ( $plugin = readdir( $dh ) ) !== false ) {
				if ( substr( $plugin, -4 ) == '.php' ) {
					$this->registered_modules[ substr( $plugin, 0, -4 ) ] = TRUE;
					require_once dirname( __FILE__ ) . '/features/' . $plugin;
				}
			}
			closedir( $dh );
		}

		/**
		 * function for custom plugins to get activated on all language blogs  
		 *
		 * @access  public
		 * @since   0.1
		 * @param   int $element_id ID of the selected element
		 * @param   string $type type of the selected element
		 * @param   int $blog_id ID of the selected blog
		 * @return  array linked elements
		 */
		function run_custom_plugin( $element_id, $type, $blog_id, $hook, $param ) {

			$this->set_source_id( $element_id, $blog_id, $type );
			$languages = $this->get_available_languages();
			$current_blog = get_current_blog_id();
			if ( 0 < count( $languages ) ) {
				foreach ( $languages as $languageid => $languagename ) {
					if ( $current_blog != $languageid ) {
						switch_to_blog( $languageid );
						$return = do_action( $hook, $param );
						restore_current_blog();
					}
				}
			}
		}

		/**
		 * function to get the url of the flag from a blogid  
		 *
		 * @access public
		 * @since 0.1
		 * @param int $blog_id ID of a blog
		 * @return string url of the language image
		 */
		function get_language_flag( $blog_id = 0 ) {

			$url = '';

			if ( 0 == $blog_id ) {
				$blog_id = get_current_blog_id();
			}

			// Custom flag image set?
			$custom_flag = get_blog_option( $blog_id, 'inpsyde_multilingual_flag_url' );
			if ( $custom_flag )
				return $custom_flag;

			$languages = get_site_option( 'inpsyde_multilingual' );
			$language_code = substr( $languages[ $blog_id ][ 'lang' ], 0, 2 ); // get the first lang element
			if ( '' != $language_code && file_exists( plugin_dir_path( __FILE__ ) . '/flags/' . $language_code . '.gif' ) ) {
				$url = plugins_url( 'flags/' . $language_code . '.gif', __FILE__ );
			}
			return $url;
		}

		/**
		 * Check state of module to determine
		 * aktivation/deactivation
		 * 
		 * @access  protected
		 * @since   0.3b
		 * @param   string $module | module handler
		 * @return  array $state | module state  
		 */
		protected function get_state_module( $module ) {

			$state = get_site_option( 'state_modules' );

			// New module?
			if ( ! ( ISSET( $state[ $module ] ) ) ) {
				$state[ $module ] = 'on';
				update_site_option( 'state_modules', $state );
				return $state[ $module ];
			}
			// Deaktivated module?
			elseif ( 'off' == $state[ $module ] )
				return $state[ $module ];
		}

		/**
		 * Delete removed blogs from site_option 'inpsyde_multilingual'
		 * and cleanup linked elements table
		 * 
		 * @param   int $blog_id 
		 * @since   0.3
		 */
		public function delete_blog( $blog_id ) {

			global $wpdb;

			// Update site_option
			$blogs = get_site_option( 'inpsyde_multilingual' );
			if ( array_key_exists( $blog_id, $blogs ) )
				unset( $blogs[ $blog_id ] );
			update_site_option( 'inpsyde_multilingual', $blogs );

			// Cleanup linked elements table
			$error = $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->link_table} WHERE ml_source_blogid = %d OR ml_blogid = %d", $blog_id, $blog_id ) );
		}

		/**
		 * Contextual help
		 *
		 * @global  type $my_plugin_hook
		 * @param   string $contextual_help
		 * @param   type $screen_id
		 * @param   type $screen
		 * @return  string $contextual_help
		 */
		function context_help( $contextual_help, $screen_id, $screen ) {
			
			$screen = get_current_screen();

			$sites = array( 'site-info-network', 'site-users-network', 'site-themes-network', 'site-settings-network' );

			if ( in_array( $screen_id, $sites ) ) {

				$content = '<p><strong>' . __( 'Choose blog language', $this->get_textdomain() ) . '</strong>';
				$content.= __( ' - Set the language and flag. You can use this to distinguish blogs and display them in the frontend using the Multilingual Press widget.', $this->get_textdomain() ) . '</p>';
				$content.= '<p><strong>' . __( 'Title', $this->get_textdomain() ) . '</strong>';
				$content.= __( ' - Alternative language file, which will be used if text field is filled.', $this->get_textdomain() ) . '</p>';
				$content.= '<p><strong>' . __( 'Blog flag image URL', $this->get_textdomain() ) . '</strong>';
				$content.= __( ' - Multilingual Press uses a default flag image, you can define a custom one here.', $this->get_textdomain() ) . '</p>';
				$content.= '<p><strong>' . __( 'Multilingual Blog Relationship', $this->get_textdomain() ) . '</strong>';
				$content.= __( ' - Determine which blogs will be interlinked. If you create a post or page, they will be automaticaly duplicated into each interlinked blog.', $this->get_textdomain() ) . '</p>';

				$content = apply_filters( 'mlp-context-help', $content );

				$screen->add_help_tab( array( 'id' => 'multilingualpress-help', 'title' => __( 'Multilingual Press' ), 'content' => $content ) );
			}
		}

	}

	if ( function_exists( 'add_action' ) ) {
		add_action( 'plugins_loaded', array( 'Inpsyde_Multilingualpress', 'get_object' ) );
		register_activation_hook( __FILE__, array( 'Inpsyde_Multilingualpress', 'install_plugin' ) );
	}
}

?>