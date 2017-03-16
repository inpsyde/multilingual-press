<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Factory\NonceFactory;
use Inpsyde\MultilingualPress\MultilingualPress;

/**
 * Controller for the basic translation meta box.
 */
class Mlp_Translation_Metabox {

	/**
	 * @var string[]
	 */
	private $allowed_post_types = [ 
		'post',
		'page',
	 ];

	/**
	 * @var Mlp_Translatable_Post_Data
	 */
	private $data;

	/**
	 * @var NonceFactory
	 */
	private $nonce_factory;

	/**
	 * Constructor.
	 */
	public function __construct() {

		if ( ! $this->is_post_editor() ) {
			return;
		}

		$this->nonce_factory = MultilingualPress::resolve( 'multilingualpress.nonce_factory' );

		/**
		 * Filter the allowed post types.
		 *
		 * @param string[]                $allowed_post_types Allowed post type names.
		 * @param Mlp_Translation_Metabox $meta_box           Translation meta box object.
		 */
		$this->allowed_post_types = (array) apply_filters(
			'mlp_allowed_post_types',
			$this->allowed_post_types,
			$this
		);

		$this->data = new Mlp_Translatable_Post_Data(
			null,
			$this->allowed_post_types,
			MultilingualPress::resolve( 'multilingualpress.content_relations_table' ),
			MultilingualPress::resolve( 'multilingualpress.content_relations' ),
			$this->nonce_factory
		);

		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ], 10, 2 );

		/**
		 * Filter whether to use an external save method instead of the built-in method.
		 *
		 * @param bool $external_save_method Use an external save method?
		 */
		$mlp_external_save_method = (bool) apply_filters( 'mlp_external_save_method', false );

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! $mlp_external_save_method ) {
			add_action( 'save_post', [ $this->data, 'save' ], 10, 2 );
		}

		$translator_init_args = [
			'allowed_post_types' => $this->allowed_post_types,
			'basic_data'         => $this->data,
			'instance'           => $this,
		 ];
		/**
		 * Runs before internal actions are registered.
		 *
		 * @param array $translator_init_args Translator arguments {
		 *                                    'allowed_post_types' => string[]
		 *                                    'basic_data'         => Mlp_Translatable_Post_Data
		 *                                    'instance'           => Mlp_Translation_Metabox
		 *                                    }
		 */
		do_action( 'mlp_post_translator_init', $translator_init_args );
	}

	/**
	 * @param string  $post_type
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function register_meta_boxes( $post_type, WP_Post $post ) {

		if ( ! in_array( $post_type, $this->allowed_post_types, true ) ) {
			return;
		}

		$current_blog_id = get_current_blog_id();

		$site_relations = MultilingualPress::resolve( 'multilingualpress.site_relations' );

		$related_blogs = $site_relations->get_related_site_ids( (int) $current_blog_id, false );

		if ( empty( $related_blogs ) ) {
			return;
		}

		foreach ( $related_blogs as $blog_id ) {
			// Do not allow translations if the user is lacking capabilities for the remote blog
			if ( ! $this->is_translatable_by_user( $post, $blog_id ) ) {
				continue;
			}

			if ( $current_blog_id !== (int) $blog_id ) {
				$this->register_metabox_per_language( $blog_id, $post );
			}
		}

		$asset_manager = MultilingualPress::resolve( 'multilingualpress.asset_manager' );
		$asset_manager->enqueue_script( 'multilingualpress-admin' );
		$asset_manager->enqueue_style( 'multilingualpress-admin' );
	}

	/**
	 * Status and, if available, publishing time.
	 *
	 * @param int     $blog_id
	 * @param WP_Post $remote_post
	 *
	 * @return string
	 */
	public function get_remote_post_info( $blog_id, WP_Post $remote_post ) {

		$existing_statuses = get_post_statuses();

		switch_to_blog( $blog_id );

		$status = get_post_status( $remote_post );

		if ( ! empty( $existing_statuses[ $status ] ) ) {
			$translated_status = $existing_statuses[ $status ];
		} else {
			$translated_status = ucfirst( $status );
		}

		if ( in_array( $status, [ 'publish', 'private' ], true ) ) {
			$template = esc_html_x(
				'%1$s (%2$s)',
				'No HTML; 1 = post status, 2 = publish time',
				'multilingual-press'
			);

			$post_time = get_post_time( get_option( 'date_format' ), false, $remote_post );

			$translated_status = sprintf( $template, $translated_status, $post_time );
		}

		restore_current_blog();

		return $translated_status;
	}

	/**
	 * Check if the current user has the appropriate capabilities to edit the given post.
	 *
	 * @param WP_Post $post
	 * @param int     $blog_id
	 *
	 * @return bool
	 */
	private function is_translatable_by_user( WP_Post $post, $blog_id ) {

		$blog_id = absint( $blog_id );

		$remote_post = $this->data->get_remote_post( $post, $blog_id );
		if ( isset( $remote_post->dummy ) && $remote_post->dummy === true ) {
			return current_user_can_for_blog( $blog_id, 'edit_posts' );
		}

		return current_user_can_for_blog( $blog_id, 'edit_post', $remote_post->ID );
	}

	/**
	 * Register one box for each connected site.
	 *
	 * @param int     $blog_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	private function register_metabox_per_language( $blog_id, WP_Post $post ) {

		$remote_post = $this->data->get_remote_post( $post, $blog_id );

		$lang = $this->data->get_remote_language( $blog_id );

		$title = $this->get_metabox_title( $blog_id, $remote_post, $lang );

		$metabox_data = [ 
			'remote_blog_id' => $blog_id,
			'remote_post'    => $remote_post,
			'language'       => $lang,
		 ];

		$view = new Mlp_Translation_Metabox_View( $this->nonce_factory->create( [
			"save_translation_of_post_{$post->ID}_for_site_$blog_id"
		] ) );

		add_meta_box(
			"inpsyde_multilingual_$blog_id",
			$title,
			[ $view, 'render' ],
			null,
			'advanced',
			'default',
			$metabox_data
		);

		if ( empty( $remote_post->dummy ) ) {
			$this->register_metabox_view_details( $view, $post, $blog_id );
		} else {
			$callback = [ $view, 'show_translation_checkbox' ];
			/**
			 * Filter the post translator activation checkbox callback.
			 *
			 * @param array|string $callback Callback name or class-method array.
			 */
			$checkbox_callback = apply_filters( 'mlp_post_translator_activation_checkbox', $callback );

			if ( $checkbox_callback ) {
				add_action( "mlp_translation_meta_box_top_$blog_id", $checkbox_callback, 10, 3 );
			}
		}

		/**
		 * Runs after registration of the meta box for the given blog's language.
		 *
		 * @param WP_Post $post    Post object.
		 * @param int     $blog_id Blog ID.
		 */
		do_action( 'mlp_translation_meta_box_registered', $post, $blog_id );
	}

	/**
	 * Create the title for each metabox.
	 *
	 * @param int     $blog_id
	 * @param WP_Post $post
	 * @param string  $language
	 *
	 * @return string
	 */
	private function get_metabox_title( $blog_id, WP_Post $post, $language ) {

		/* translators: 1: site name, 2: language */
		$text = esc_html__( 'Translation for %1$s (%2$s)', 'multilingual-press' );

		$site_name = get_blog_option( $blog_id, 'blogname' );
		$title     = sprintf( $text, $site_name, $language );

		if ( ! empty( $post->dummy ) ) {
			// this is a fake post
			return $title;
		}

		$extra = $this->get_remote_post_info( $blog_id, $post );
		$title .= $this->get_edit_post_link( $post->ID, $blog_id, $extra );

		return $title;
	}

	/**
	 * Register separate input fields.
	 *
	 * @param Mlp_Translation_Metabox_View $view
	 * @param WP_Post                      $post
	 * @param int                          $blog_id
	 *
	 * @return void
	 */
	private function register_metabox_view_details( Mlp_Translation_Metabox_View $view, WP_Post $post, $blog_id ) {

		$callbacks = [ 
			'title'  => [ $view, 'show_title' ],
			'editor' => [ $view, 'show_editor' ],
		 ];

		/**
		 * Filter the meta box view callbacks.
		 *
		 * @param array   $callbacks Array of callback names or class-method arrays.
		 * @param WP_Post $post      Post object.
		 * @param int     $blog_id   Blog ID.
		 */
		$callbacks = apply_filters( 'mlp_translation_meta_box_view_callbacks', $callbacks, $post, $blog_id );
		if ( empty( $callbacks ) ) {
			return;
		}

		if ( ! empty( $callbacks['title'] ) && post_type_supports( $post->post_type, 'title' ) ) {
			add_action( 'mlp_translation_meta_box_top_' . $blog_id, $callbacks['title'], 10, 3 );
		}

		if ( ! empty( $callbacks['editor'] ) && post_type_supports( $post->post_type, 'editor' ) ) {
			add_action( 'mlp_translation_meta_box_main_' . $blog_id, $callbacks['editor'], 10, 3 );
		}

	}

	/**
	 * Are we on a post editor screen?
	 *
	 * @return bool
	 */
	private function is_post_editor() {

		global $pagenow;

		if ( empty( $pagenow ) ) {
			return false;
		}

		return in_array( $pagenow, [ 'post-new.php', 'post.php' ], true );
	}

	/**
	 * Used for the metabox title.
	 *
	 * @param int    $post_id
	 * @param int    $blog_id
	 * @param string $text
	 *
	 * @return string
	 */
	private function get_edit_post_link( $post_id, $blog_id, $text = '' ) {

		switch_to_blog( $blog_id );
		$url = get_edit_post_link( $post_id );
		restore_current_blog();

		if ( '' === $text ) {
			$text = __( 'Switch to site', 'multilingual-press' );
		}

		return ' <small> - <a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a></small>';
	}
}
