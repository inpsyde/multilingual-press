<?php
/**
 * Dashboard widget for incomplete translations.
 *
 * @version 2014.07.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Dashboard_Widget {

	/**
	 * @var Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * Constructor
	 *
	 * @param Inpsyde_Property_List_Interface $data
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->plugin_data = $data;

		// Register Translated Post Meta to the submit box
		add_filter( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );

		// Register the Dashboard Widget
		add_filter( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );

		// Own save method
		add_filter( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Displays the checkbox for the post translated meta
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	get_post_meta
	 * @return	void
	 */
	public function post_submitbox_misc_actions() {

		$post_id       = $this->get_post_id();
		$is_translated = $this->is_translated( $post_id );

		$context = array (
			'post_id'       => $post_id,
			'is_translated' => $is_translated,
		);

		/**
		 * Filter the visibility of the 'Translation completed' checkbox.
		 *
		 * @param bool  $show_checkbox Show the checkbox?
		 * @param array $context       Post context. {
		 *                             'post_id'       => int
		 *                             'is_translated' => bool
		 *                             }
		 *
		 * @return bool
		 */
		$show_checkbox = (bool) apply_filters( 'mlp_show_translation_completed_checkbox', TRUE, $context );
		if ( ! $show_checkbox ) {
			return;
		}
		?>
		<div class="misc-pub-section">
			<label for="post_is_translated">
				<input type="hidden" name="post_is_translated_blogid" value="<?php echo get_current_blog_id(); ?>" />
				<input type="checkbox" id="post_is_translated" value="1" name="_post_is_translated"<?php checked( TRUE, $is_translated );  ?> />
				<?php _e( 'Translation completed', 'multilingualpress' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Registers the dashboard widget
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	user_can, get_current_user_id, wp_register_sidebar_widget
	 * @return	void
	 */
	public function wp_dashboard_setup() {

		/**
		 * Filter the capability required to view the dashboard widget.
		 *
		 * @param string $capability Capability required to view the dashboard widget.
		 *
		 * @return string
		 */
		$capability = apply_filters( 'mlp_dashboard_widget_access', 'edit_others_posts' );

		if ( current_user_can( $capability ) )
			wp_add_dashboard_widget(
				'multilingualpress-dashboard-widget',
				__( 'Untranslated Posts', 'multilingualpress' ),
				array( $this, 'dashboard_widget' )
			);
	}

	/**
	 * Displays the posts which are not translated yet
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	get_option, get_site_option, $wpdb, get_the_time, get_the_title, admin_url,
	 * 			switch_to_blog, restore_current_blog, get_current_blog_id
	 * @return	void
	 */
	public function dashboard_widget() {

		$site_relations = $this->plugin_data->get( 'site_relations' );

		$related_blogs = $site_relations->get_related_sites( 0, FALSE );

		// We have no related blogs so we can stop here
		if ( ! is_array( $related_blogs ) ) {
			echo '<p>' . __( 'Sorry, there are no connected sites in the system for this site.', 'multilingualpress' ) . '</p>';
			return;
		}

		?>
		<table class="widefat">
		<?php

		$related_blogs = array_unique( $related_blogs );

		// Let's run each blog to get the posts
		foreach ( $related_blogs as $blog_to_translate ) {
			switch_to_blog( $blog_to_translate );

			?><tr><th colspan="3"><strong><?php _e( 'Pending Translations for', 'multilingualpress' ); ?> <?php bloginfo( 'name' ); ?></strong></th></tr><?php

			global $wpdb;

			$query = 'SELECT * FROM ' . $wpdb->posts . ' p INNER JOIN ' . $wpdb->postmeta . ' pm ON
							pm.post_id = p.ID WHERE
							( pm.meta_key = "post_is_translated" OR pm.meta_key = "_post_is_translated" ) AND
							pm.meta_value = "0" AND
							p.post_status != "trash"
						ORDER BY
							p.post_date';
			$posts_to_translate = $wpdb->get_results( $query );

			if ( 0 < count( $posts_to_translate ) ) {
				foreach ( $posts_to_translate as $post ) {
					?>
					<tr>
						<td style="width:20%;"><a href="<?php echo admin_url(); ?>post.php?post=<?php echo $post->ID; ?>&action=edit"><?php _e( 'Translate', 'multilingualpress' ); ?></a></td>
						<td style="width:55%;"><?php echo get_the_title( $post->ID ); ?></td>
						<td style="width:25%;"><?php echo get_the_time( get_option( 'date_format' ), $post->ID ); ?></td>
					</tr>
					<?php
				}
			}
			restore_current_blog();
		}
		?>
		</table>
		<?php
	}

	/**
	 * update post meta
	 *
	 * @param   int $post_id ID of the post
	 * @return  void
	 */
	public function save_post( $post_id ) {

		// We're only interested in published posts at this time
		$post_status = get_post_status( $post_id );
		if ( 'publish' !== $post_status && 'draft' !== $post_status )
			return;

		// Avoid recursion:
		// wp_insert_post() invokes the save_post hook, we check the current blog_id
		// against the hidden post variable for the blog_id.
		if ( ! isset( $_POST[ 'post_is_translated_blogid' ] ) || $_POST[ 'post_is_translated_blogid' ] != get_current_blog_id() )
			return;

		// If checkbox is not checked, return
		if ( isset( $_POST[ 'translate_this_post' ] ) )
			return;

		delete_post_meta( $post_id, 'post_is_translated' );

		// Well, is this post translated? we just need the single way
		if ( isset( $_POST[ '_post_is_translated' ] ) && '1' == $_POST[ '_post_is_translated' ] )
			update_post_meta( $post_id, '_post_is_translated', '1' );
		else
			update_post_meta( $post_id, '_post_is_translated', '0' );
	}

	/**
	 * Get the current post ID or 0
	 *
	 * @return int
	 */
	private function get_post_id() {

		if ( ! isset ( $_GET[ 'post' ] ) )
			return 0;

		return absint( $_GET[ 'post' ] );
	}

	/**
	 * Check if the current post is translated already
	 *
	 * @param  int $post_id
	 * @return bool
	 */
	private function is_translated( $post_id ) {

		if ( ! $post_id )
			return FALSE;

		if ( (int) get_post_meta( $_GET[ 'post' ], '_post_is_translated', TRUE ) )
			return TRUE;

		// old key
		if ( (int) get_post_meta( $post_id, 'post_is_translated', TRUE ) )
			return TRUE;

		return FALSE;
	}

}
