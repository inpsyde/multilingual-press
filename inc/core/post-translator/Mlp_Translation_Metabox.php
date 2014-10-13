<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Translation_Metabox
 *
 * Controller for the basic translation metabox set up.
 *
 * @version 2014.01.15
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Translation_Metabox {

	/**
	 * @var string
	 */
	private $key = 'mlp_translation_metabox';

	/**
	 * @var Inpsyde_Nonce_Validator
	 */
	private $nonce;

	/**
	 * @var Mlp_Save_Post_Request_Validator
	 */
	private $request_validator;

	/**
	 * @var Mlp_Translatable_Post_Data
	 */
	private $data;

	/**
	 * @var Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * @var array
	 */
	private $allowed_post_types = array ( 'post', 'page' );

	/**
	 * Constructor.
	 *
	 * @param Inpsyde_Property_List_Interface $plugin_data
	 */
	public function __construct( Inpsyde_Property_List_Interface $plugin_data ) {

		if ( ! $this->is_post_editor() )
			return;

		$this->plugin_data       = $plugin_data;
		$this->nonce             = new Inpsyde_Nonce_Validator( $this->key, get_current_blog_id() );
		$this->request_validator = new Mlp_Save_Post_Request_Validator( $this->nonce );

		/**
		 * Filter to add or delete allowed post_types
		 *
		 * @param   Array $allowed_post_types
		 * @param   Mlp_Translation_Metabox
		 */
		$this->allowed_post_types = (array) apply_filters(
			'mlp_allowed_post_types',
			$this->allowed_post_types,
			$this
		);

		$this->data = new Mlp_Translatable_Post_Data(
			$this->request_validator,
			$this->allowed_post_types,
			$this->plugin_data->link_table,
			$this->plugin_data->content_relations
		);

		add_action( 'add_meta_boxes', array ( $this, 'register_meta_boxes' ), 10, 2 );

		/**
		 * Filter to remove the save_post-Action and to implement your own logic
		 * @param   Boolean false
		 */
		$mlp_external_save_method = (bool) apply_filters( 'mlp_external_save_method', FALSE );

		if ( 'POST' === $_SERVER[ 'REQUEST_METHOD' ]
			&& ! $mlp_external_save_method
		)
			add_action( 'save_post', array ( $this->data, 'save' ), 10, 2 );

		$translator_init_args = array (
			'nonce'              => $this->nonce,
			'request_validator'  => $this->request_validator,
			'allowed_post_types' => $this->allowed_post_types,
			'basic_data'         => $this->data,
			'instance'           => $this
		);
		/**
		 * Hook to add more options to the meta boxes.
		 *
		 * @param   array $translator_init_args array(
		 *      'nonce'                 => String
		 *      'request_validator'     => Mlp_Save_Post_Request_Validator,
		 *      'allowed_post_types'    => Array
		 *      'basic_data'            => Mlp_Translatable_Post_Data,
		 *      'instance'           	=> Mlp_Translation_Metabox
		 * )
		 */
		do_action(
			'mlp_post_translator_init',
			$translator_init_args
		);
	}

	/**
	 * @param  string  $post_type
	 * @param  WP_Post $post
	 * @return void
	 */
	public function register_meta_boxes( $post_type, WP_Post $post ) {

		if ( ! in_array( $post_type, $this->allowed_post_types ) )
			return;

		$current_blog_id = get_current_blog_id();
		$related_blogs = $this->plugin_data->site_relations->get_related_sites( $current_blog_id, FALSE );

		if ( empty ( $related_blogs ) )
			return;

		foreach ( $related_blogs as $blog_id ) {

			if ( $current_blog_id !== (int) $blog_id )
				$this->register_metabox_per_language( $blog_id, $post );
		}

		$this->plugin_data->assets->provide( 'mlp_backend_css' );
		$this->plugin_data->assets->provide( 'mlp_backend_js' );
	}

	/**
	 * Register one box for each connected site.
	 *
	 * @param  int     $blog_id
	 * @param  WP_Post $post
	 * @return void
	 */
	private function register_metabox_per_language( $blog_id, WP_Post $post ) {

		$view        = new Mlp_Translation_Metabox_View( $this->nonce );
		$lang        = $this->data->get_remote_language( $blog_id );
		$remote_post = $this->data->get_remote_post( $post, $blog_id );
		$title       = $this->get_metabox_title( $blog_id, $remote_post, $lang );

		$metabox_data = array (
			'remote_blog_id' => $blog_id,
			'remote_post'    => $remote_post,
			'language'       => $lang
		);

		add_meta_box(
			"inpsyde_multilingual_$blog_id",
			$title,
			array ( $view, 'render' ),
			NULL,
			'advanced',
			'default',
			$metabox_data
		);

		if ( empty ( $remote_post->dummy ) ) {
			$this->register_metabox_view_details( $view, $post, $blog_id );
		}
		else {
			$checkbox_callback = apply_filters(
				'mlp_post_translator_activation_checkbox',
				array ( $view, 'show_translation_checkbox' )
			);

			if ( $checkbox_callback )
				add_action( "mlp_translation_meta_box_top_$blog_id", $checkbox_callback, 10, 3 );
		}

		/**
		 * Runs once per language.
		 *
		 * @param WP_Post $post
		 * @param int     $blog_id
		 */
		do_action( 'mlp_translation_meta_box_registered', $post, $blog_id );
	}

	/**
	 * Create the title for each metabox.
	 *
	 * @param  int     $blog_id
	 * @param  WP_Post $post
	 * @param  string  $language
	 * @return string
	 */
	private function get_metabox_title( $blog_id, WP_Post $post, $language ) {

		$text = esc_html_x(
			'Translation for %1$s (%2$s)',
			'No HTML here. 1 = site name, 2 = language',
			'multilingualpress'
		);

		$site_name = get_blog_option( $blog_id, 'blogname' );
		$title     = sprintf( $text, $site_name, $language );

		if ( ! empty ( $post->dummy ) ) // this is a fake post
			return $title;

		$extra = $this->get_remote_post_info( $blog_id, $post );
		$title .= $this->get_edit_post_link( $post->ID, $blog_id, $extra );

		return $title;
	}

	/**
	 * Register separate input fields.
	 *
	 * @param  Mlp_Translation_Metabox_View $view
	 * @param  WP_Post                      $post
	 * @param  int                          $blog_id
	 * @return void
	 */
	private function register_metabox_view_details(
		Mlp_Translation_Metabox_View $view,
		WP_Post $post,
		$blog_id
	) {
		$callbacks = array (
			'title'   => array ( $view, 'show_title' ),
			'editor'  => array ( $view, 'show_editor' ),
			'upgrade' => array ( $view, 'show_upgrade_notice' )
		);

		/**
		 * You can change the default actions here.
		 *
		 * @param array   $callbacks
		 * @param WP_Post $post
		 * @param int     $blog_id
		 */
		$callbacks = apply_filters(
			'mlp_translation_meta_box_view_callbacks',
			$callbacks,
			$post,
			$blog_id
		);


		if ( empty ( $callbacks ) )
			return;

		if ( ! empty ( $callbacks[ 'title' ] ) && post_type_supports( $post->post_type, 'title' ) )
			add_action( 'mlp_translation_meta_box_top_' . $blog_id, $callbacks[ 'title' ], 10, 3 );

		if ( ! empty ( $callbacks[ 'editor' ] ) && post_type_supports( $post->post_type, 'editor' ) )
			add_action( 'mlp_translation_meta_box_main_' . $blog_id, $callbacks[ 'editor' ], 10, 3 );

		if ( ! empty ( $callbacks[ 'upgrade' ] )
			&& 'MultilingualPress Pro' !== $this->plugin_data->plugin_name
		)
			add_action( 'mlp_translation_meta_box_bottom_' . $blog_id, $callbacks[ 'upgrade' ], 10, 3 );
	}

	/**
	 * Are we on a post editor screen?
	 *
	 * @return bool
	 */
	private function is_post_editor() {

		global $pagenow;

		if ( empty ( $pagenow ) )
			return FALSE;

		return in_array( $pagenow, array ( 'post-new.php', 'post.php' ) );
	}

	/**
	 * Used for the metabox title.
	 *
	 * @param  int    $post_id
	 * @param  int    $blog_id
	 * @param  string $text
	 * @return string
	 */
	private function get_edit_post_link( $post_id, $blog_id, $text = '' ) {

		switch_to_blog( $blog_id );
		$url = get_edit_post_link( $post_id );
		restore_current_blog();

		if ( '' === $text )
			$text = esc_html__( 'Switch to site', 'multilingualpress' );

		return " <small> - <a href='$url'>$text</a></small>";
	}

	/**
	 * Status and, if available, publishing time.
	 *
	 * @param  int     $blog_id
	 * @param  WP_Post $remote_post
	 * @return string
	 */
	public function get_remote_post_info( $blog_id, WP_Post $remote_post ) {

		$existing_statuses = get_post_statuses();

		switch_to_blog( $blog_id );

		$status = get_post_status( $remote_post );

		if ( ! empty ( $existing_statuses[ $status ] ) )
			$translated_status = $existing_statuses[ $status ];
		else
			$translated_status = ucfirst( $status );

		if ( in_array( $status, array ( 'publish', 'private' ) ) ) {

			$post_time = get_post_time(
				get_option( 'date_format' ),
				FALSE,
				$remote_post
			);

			$template          = esc_html_x(
				'%1$s (%2$s)',
				'No HTML; 1 = post status, 2 = publish time',
				'multilingualpress'
			);
			$translated_status = sprintf( $template, $translated_status, $post_time );
		}

		restore_current_blog();

		return $translated_status;
	}
}