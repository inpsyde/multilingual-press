<?php # -*- coding: utf-8 -*-

/**
 * Provides a new post meta and checkbox to trash the posts through the related blogs.
 */
class Mlp_Trasher {

	/**
	 * @var Mlp_Module_Manager_Interface
	 */
	private $module_manager;

	/**
	 * @var Inpsyde_Nonce_Validator
	 */
	private $nonce_validator;

	/**
	 * @var bool
	 */
	private $saved_post = false;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param Mlp_Module_Manager_Interface $module_manager Module manager object.
	 */
	public function __construct( Mlp_Module_Manager_Interface $module_manager ) {

		$this->module_manager = $module_manager;

		$this->nonce_validator = Mlp_Nonce_Validator_Factory::create( 'save_trasher_setting' );
	}

	/**
	 * Wires up all functions.
	 *
	 * @return void
	 */
	public function initialize() {

		// Quit here if module is turned off.
		if ( ! $this->register_setting() ) {
			return;
		}

		// Register Trasher post meta to the submit box.
		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );

		// Trash and delete the post method before WordPress 3.2.0.
		add_action( 'trash_post', array( $this, 'trash_post' ) );

		// Trash and delete the post method after WordPress 3.2.0.
		add_action( 'wp_trash_post', array( $this, 'trash_post' ) );

		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Displays the checkbox for the Trasher post meta.
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function post_submitbox_misc_actions() {

		$post_id = absint( filter_input( INPUT_GET, 'post' ) );
		if ( $post_id ) {
			// old key
			$trash_the_other_posts = (int) get_post_meta( $post_id, 'trash_the_other_posts', true );

			if ( 1 !== $trash_the_other_posts ) {
				$trash_the_other_posts = (int) get_post_meta( $post_id, '_trash_the_other_posts', true );
			}
		} else {
			$trash_the_other_posts = false;
		}
		?>
		<div class="misc-pub-section curtime misc-pub-section-last">
			<?php wp_nonce_field( $this->nonce_validator->get_action(), $this->nonce_validator->get_name() ); ?>
			<label for="trash_the_other_posts">
				<input type="checkbox" id="trash_the_other_posts" name="_trash_the_other_posts"
					<?php checked( 1, $trash_the_other_posts ); ?>>
				<?php
				esc_html_e( 'Send all the translations to trash when this post is trashed.', 'multilingual-press' );
				?>
			</label>
		</div>
		<?php
	}

	/**
	 * Trashes the related posts if the user wants to.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function trash_post( $post_id ) {

		$trash_the_other_posts = (int) get_post_meta( $post_id, '_trash_the_other_posts', true );

		// old key
		if ( 1 !== $trash_the_other_posts ) {
			$trash_the_other_posts = (int) get_post_meta( $post_id, 'trash_the_other_posts', true );
		}

		if ( 1 !== $trash_the_other_posts ) {
			return;
		}

		// remove filter to avoid recursion
		remove_filter( current_filter(), array( $this, __FUNCTION__ ) );

		$linked_posts = mlp_get_linked_elements( $post_id );
		foreach ( $linked_posts as $linked_blog => $linked_post ) {
			switch_to_blog( $linked_blog );

			wp_trash_post( $linked_post );

			restore_current_blog();
		}

		add_filter( current_filter(), array( $this, __FUNCTION__ ) );
	}

	/**
	 * Updates the post meta.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function save_post( $post_id ) {

		if ( ! $this->nonce_validator->is_valid() ) {
			return;
		}

		// We're only interested in published posts at this time
		$post_status = get_post_status( $post_id );
		if ( ! in_array( $post_status, array( 'publish', 'draft' ), true ) ) {
			return;
		}

		// The wp_insert_post() method fires the save_post action hook, so we have to avoid recursion.
		if ( $this->saved_post ) {
			return;
		} else {
			$this->saved_post = true;
		}

		// old key
		delete_post_meta( $post_id, 'trash_the_other_posts' );

		$trash_the_other_posts = 'on' === filter_input( INPUT_POST, '_trash_the_other_posts' );

		// Should the other post also been trashed?
		if ( $trash_the_other_posts ) {
			update_post_meta( $post_id, '_trash_the_other_posts', '1' );
		} else {
			update_post_meta( $post_id, '_trash_the_other_posts', '0' );
		}

		// Get linked posts
		$linked_posts = mlp_get_linked_elements( $post_id );
		foreach ( $linked_posts as $linked_blog => $linked_post ) {
			switch_to_blog( $linked_blog );

			delete_post_meta( $linked_post, 'trash_the_other_posts' );

			// Should the other post also been trashed?
			update_post_meta( $linked_post, '_trash_the_other_posts', $trash_the_other_posts ? '1' : '0' );

			restore_current_blog();
		}
	}

	/**
	 * Registers the UI for the module manager.
	 *
	 * @return bool
	 */
	private function register_setting() {

		$description = __(
			'This module provides a new post meta and checkbox to trash the posts. If you enable the checkbox and move a post to the trash MultilingualPress also will trash the linked posts.',
			'multilingual-press'
		);

		return $this->module_manager->register( array(
			'display_name' => __( 'Trasher', 'multilingual-press' ),
			'slug'         => 'class-' . __CLASS__,
			'description'  => $description,
		) );
	}
}
