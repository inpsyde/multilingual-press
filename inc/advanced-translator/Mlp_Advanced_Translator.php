<?php
/**
 * Class Mlp_Advanced_Translator
 *
 * @version 2014.10.10
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Advanced_Translator {

	/**
	 * Passed by main controller.
	 *
	 * @type Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * @type Mlp_Advanced_Translator_Data
	 */
	private $translation_data;

	/**
	 * @type Mlp_Translatable_Post_Data_Interface
	 */
	private $basic_data;

	/**
	 * The view class.
	 *
	 * @type Mlp_Advanced_Translator_View
	 */
	private $view;

	/**
	 * Constructor
	 *
	 * @param  Inpsyde_Property_List_Interface $data
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->plugin_data = $data;

		// Quit here if module is turned off
		if ( ! $this->register_setting() )
			return;

		add_action( 'mlp_post_translator_init', array ( $this, 'setup' ) );
		add_filter( 'mlp_external_save_method', '__return_true' );

		// Disable default actions
		add_action(
			'mlp_translation_meta_box_registered',
			array ( $this, 'register_metabox_view_details' ),
			10,
			2
		);
	}

	/**
	 * @wp-hook mlp_post_translator_init
	 * @param  array $base_data
	 * @return void
	 */
	public function setup( Array $base_data ) {

		$this->translation_data = new Mlp_Advanced_Translator_Data(
			$base_data['request_validator'],
			$base_data['basic_data'],
			$base_data['allowed_post_types'],
			$this->plugin_data->site_relations
		);
		$this->basic_data = $base_data['basic_data'];
		$this->view       = new Mlp_Advanced_Translator_View( $this->translation_data );

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] )
			add_action( 'save_post', array( $this->translation_data, 'save' ), 10, 2 );

		// Disable the checkbox, we can translate auto-drafts.
		add_filter( 'mlp_post_translator_activation_checkbox', '__return_false' );
		add_filter( 'mlp_translation_meta_box_view_callbacks', '__return_empty_array' );
	}

	/**
	 *
	 * @wp-hook mlp_translation_meta_box_registered
	 * @param  WP_Post $post
	 * @param  int     $blog_id
	 * @return void
	 */
	public function register_metabox_view_details( WP_Post $post, $blog_id ) {

		// get the current remote post status
		$remote_post = $this->basic_data->get_remote_post( $post, $blog_id );
		$is_trashed  = isset( $remote_post->post_status ) && $remote_post->post_status == 'trash';

		// set the base
		$base = 'mlp_translation_meta_box_';

		// check if the remote post is trashed
		// if it is so, show the warning
		if ( $is_trashed ) {
			add_action( $base . 'top_' . $blog_id, array ( $this->view, 'show_trashed_message' ), 10, 3 );
			return;
		}

		// add the actions if the remote is not trashed
		add_action( $base . 'top_' . $blog_id, array ( $this->view, 'blog_id_input' ), 10, 3 );

		if ( post_type_supports( $post->post_type, 'title' ) )
			add_action( $base . 'top_' . $blog_id, array ( $this->view, 'show_title' ), 10, 3 );

		if ( post_type_supports( $post->post_type, 'editor' ) )
			add_action( $base . 'main_' . $blog_id, array ( $this->view, 'show_editor' ), 10, 3 );
		else
			remove_action( 'media_buttons', array ( $this->view, 'show_copy_button' ), 20 );


		if ( post_type_supports( $post->post_type, 'thumbnail' ) )
			add_action( $base . 'main_' . $blog_id, array ( $this->view, 'show_thumbnail_checkbox' ), 11, 3 );

		$taxonomies = get_object_taxonomies( $post, 'objects' );

		if ( ! empty ( $taxonomies ) )
			add_action( $base . 'bottom_' . $blog_id, array ( $this->view, 'show_taxonomies' ), 10, 3 );
	}

	/**
	 * @return bool
	 */
	private function register_setting() {

		$desc = __(
			'Use the WYSIWYG editor to write all translations on one screen, including thumbnails and taxonomies.',
			'multilingualpress'
		);

		return $this->plugin_data->module_manager->register(
			array (
				'display_name' => __( 'Advanced Translator', 'multilingualpress' ),
				'slug'         => 'class-' . __CLASS__,
				'description'  => $desc
			)
		);
	}
}