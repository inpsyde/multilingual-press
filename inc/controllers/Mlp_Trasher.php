<?php
/**
 * Module Name:	MultilingualPress Trasher
 * Description:	This Trasher provides a new post meta and checkbox to trash the posts through the related blogs
 * Author:		Inpsyde GmbH
 * Version:		2013.12.04
 * Author URI:	http://inpsyde.com
 */

class Mlp_Trasher {

	/**
	 * Passed by main controller.
	 *
	 * @type Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * Used in save_post() to prevent recursion
	 *
	 * @static
	 * @since	0.3
	 * @var		NULL | integer
	 */
	private static $source_blog = NULL;

	/**
	 * @param Inpsyde_Property_List_Interface $data
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->plugin_data = $data;

		// Quit here if module is turned off
		if ( ! $this->register_setting() )
			return;

		// Register Translated Post Meta to the submit box
		add_filter( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );

		// Trash and delete the post method
		add_filter( 'trash_post', array( $this, 'trash_post' ) );

		// Trash and delete the post method after WP 3.2
		add_filter( 'wp_trash_post', array( $this, 'trash_post' ) );

		// Own save method
		add_filter( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Register our UI for the module manager.
	 *
	 * @return bool
	 */
	private function register_setting() {

		/** @var Mlp_Module_Manager_Interface $module_manager */
		$module_manager = $this->plugin_data->get( 'module_manager' );

		$display_name = __( 'Trasher', 'multilingualpress' );

		$description = __(
			'This module provides a new post meta and checkbox to trash the posts. If you enable the checkbox and move a post to the trash MultilingualPress also will trash the linked posts.',
			'multilingualpress'
		);

		return $module_manager->register(
			array(
				'display_name' => $display_name,
				'slug'         => 'class-' . __CLASS__,
				'description'  => $description,
			)
		);
	}

	/**
	 * Displays the checkbox for the post translated meta
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	get_post_meta, _e
	 * @return	void
	 */
	public function post_submitbox_misc_actions() {

		if ( isset( $_GET[ 'post' ] ) ) {
			// old key
			$trash_the_other_posts = (int) get_post_meta( $_GET[ 'post' ], 'trash_the_other_posts', TRUE );

			if ( 1 !== $trash_the_other_posts )
				$trash_the_other_posts = (int) get_post_meta( $_GET[ 'post' ], '_trash_the_other_posts', TRUE );
		} else {
			$trash_the_other_posts = FALSE;
		}
		?>
		<div class="misc-pub-section curtime misc-pub-section-last">
			<input type="hidden" name="trasher_box" value="1" />
			<input type="checkbox" id="trash_the_other_posts" name="_trash_the_other_posts"<?php checked( 1, $trash_the_other_posts ); ?> />
			<label for="trash_the_other_posts"><?php _e( 'Send all the translations to trash when this post is trashed.', 'multilingualpress' ); ?></label>

		</div>
		<?php
	}

	/**
	 * Trashes the related posts if the user want to
	 *
	 * @param  int $post_id
	 * @return void
	 */
	public function trash_post( $post_id ) {

		$trash_the_other_posts = (int) get_post_meta( $post_id, '_trash_the_other_posts', TRUE );

		// old key
		if ( 1 !== $trash_the_other_posts )
			$trash_the_other_posts = (int) get_post_meta( $post_id, 'trash_the_other_posts', TRUE );

		if ( 1 !== $trash_the_other_posts )
			return;

		$linked_posts = mlp_get_linked_elements( $post_id );

		// remove filter to avoid recursion
		remove_filter( current_filter(), array( $this, __FUNCTION__ ) );

		foreach ( $linked_posts as $linked_blog => $linked_post ) {
			switch_to_blog( $linked_blog );
			wp_trash_post( $linked_post );
			restore_current_blog();
		}
		add_filter( current_filter(), array( $this, __FUNCTION__ ) );
	}

	/**
	 * update post meta
	 *
	 * @param   int $post_id ID of the post
	 * @return  void
	 */
	public function save_post( $post_id ) {

		// leave function if box was not available
		if ( ! isset ( $_POST[ 'trasher_box' ] ) )
			return;

		// We're only interested in published posts at this time
		$post_status = get_post_status( $post_id );
		if ( 'publish' !== $post_status && 'draft' !== $post_status )
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

		// old key
		delete_post_meta( $post_id, 'trash_the_other_posts' );
		$trash_the_other_posts = FALSE;

		// Should the other post also been trashed?
		if ( isset( $_POST[ '_trash_the_other_posts' ] ) && 'on' == $_POST[ '_trash_the_other_posts' ] ) {
			update_post_meta( $post_id, '_trash_the_other_posts', '1' );
			$trash_the_other_posts = TRUE;
		} else {
			update_post_meta( $post_id, '_trash_the_other_posts', '0' );
		}

		// Get linked posts
		$linked_posts = mlp_get_linked_elements( $post_id );

		foreach ( $linked_posts as $linked_blog => $linked_post ) {
			switch_to_blog( $linked_blog );
			delete_post_meta( $linked_post, 'trash_the_other_posts' );

			// Should the other post also been trashed?
			if ( $trash_the_other_posts )
				update_post_meta( $linked_post, '_trash_the_other_posts', '1' );
			else
				update_post_meta( $linked_post, '_trash_the_other_posts', '0' );

			restore_current_blog();
		}
	}

}
