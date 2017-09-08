<?php
/**
 * Module Name: MultilingualPress Custom Post Type Module
 * Description: Allow MlP functionality for specific custom post types
 * Author:      Inpsyde GmbH
 * Version:     0.9
 * Author URI:  http://inpsyde.com
 */

class Mlp_Cpt_Translator implements Mlp_Updatable {

	/**
	 * Registered post types
	 *
	 * @access  private
	 * @since   0.1
	 * @var     array $post_types
	 */
	private $post_types;

	/**
	 * Prefix for 'name' attribute in form fields.
	 *
	 * @type string
	 */
	private $form_name = 'mlp_cpts';

	/**
	 * Passed by main controller.
	 *
	 * @type Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * @var Inpsyde_Nonce_Validator
	 */
	private $nonce_validator;

	/**
	 * Constructor
	 *
	 * @param  Inpsyde_Property_List_Interface $data
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->plugin_data = $data;
		$this->nonce_validator = Mlp_Nonce_Validator_Factory::create( 'save_cpt_translator_settings' );

		// Quit here if module is turned off
		if ( ! $this->register_setting() ) {
			return;
		}

		add_filter( 'mlp_allowed_post_types', array( $this, 'filter_allowed_post_types' ) );

		add_action( 'mlp_modules_add_fields', array( $this, 'draw_options_page_form_fields' ) );
		// Use this hook to handle the user input of your modules' options page form fields
		add_action( 'mlp_modules_save_fields', array( $this, 'save_options_page_form_fields' ) );

		// replace the permalink if selected
		add_action( 'mlp_before_link', array( $this, 'before_mlp_link' ) );
		add_action( 'mlp_after_link', array( $this, 'after_mlp_link' ) );
	}

	/**
	 * Filter the list of allowed post types for translations.
	 *
	 * @wp-hook mlp_allowed_post_types
	 * @param   array $post_types
	 * @return  array
	 */
	public function filter_allowed_post_types( array $post_types ) {

		return array_merge( $post_types, $this->get_active_post_types() );
	}

	/**
	 * Register our UI for the module manager.
	 *
	 * @return bool
	 */
	private function register_setting() {

		/** @var Mlp_Module_Manager_Interface $module_manager */
		$module_manager = $this->plugin_data->get( 'module_manager' );

		$display_name = __( 'Custom Post Type Translator', 'multilingual-press' );

		$description = __(
			'Enable translation of custom post types. Creates a second settings box below this. The post types must be activated for the whole network or on the main site.',
			'multilingual-press'
		);

		return $module_manager->register(
			array(
				'display_name' => $display_name,
				'slug'         => 'class-' . __CLASS__,
				'description'  => $description,
				'callback'     => array( $this, 'extend_settings_description' ),
			)
		);
	}

	/**
	 * Explain when there are no custom post types.
	 *
	 * @return string
	 */
	public function extend_settings_description() {

		$found = $this->get_custom_post_types();

		if ( empty( $found ) ) {
			return '<p class="mlp-callback-indent"><em>'
				. __( 'No custom post type found.', 'multilingual-press' )
				. '</em></p>';
		}

		return '';
	}

	/**
	 * This is the callback of the metabox
	 * used to display the modules options page
	 * form fields
	 *
	 * @return  void
	 */
	public function draw_options_page_form_fields() {

		$post_types = $this->get_custom_post_types();

		if ( empty( $post_types ) ) {
			return;
		}

		$data = new Mlp_Cpt_Translator_Extra_General_Settings_Box_Data(
			$this,
			$this->nonce_validator
		);
		$box  = new Mlp_Extra_General_Settings_Box( $data );
		$box->print_box();
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|void Either a value, or void for actions.
	 */
	public function update( $name ) {

		if ( 'custom.post-type.list' === $name ) {
			return $this->get_custom_post_types();
		}

		return '';
	}

	/**
	 * Hook into mlp_settings_save_fields to handle module user input.
	 *
	 * @wp-hook mlp_settings_save_fields
	 * @return bool
	 */
	public function save_options_page_form_fields() {

		if ( ! $this->nonce_validator->is_valid() ) {
			return false;
		}

		$options    = get_site_option( 'inpsyde_multilingual_cpt' );
		$post_types = $this->get_custom_post_types();

		if ( empty( $post_types ) || empty( $_POST[ $this->form_name ] ) ) {
			$options['post_types'] = array();
			return update_site_option( 'inpsyde_multilingual_cpt', $options );
		}

		foreach ( $post_types as $cpt => $cpt_params ) {

			if ( empty( $_POST[ $this->form_name ][ $cpt ] ) ) {
				$options['post_types'][ $cpt ] = 0;
			} elseif ( empty( $_POST[ $this->form_name ][ $cpt . '|links' ] ) ) {
				$options['post_types'][ $cpt ] = 1;
			} else {
				$options['post_types'][ $cpt ] = 2;
			}
		}

		return update_site_option( 'inpsyde_multilingual_cpt', $options );
	}

	/**
	 * Returns all custom post types.
	 *
	 * @return array
	 */
	public function get_custom_post_types() {

		if ( is_array( $this->post_types ) ) {
			return $this->post_types;
		}

		$this->post_types = get_post_types( array(
			'_builtin' => false,
			'show_ui'  => true,
		), 'objects' );
		if ( $this->post_types ) {
			uasort( $this->post_types, array( $this, 'sort_cpts_by_label' ) );
		}

		return $this->post_types;
	}

	/**
	 * Sort post types by their display label.
	 *
	 * @param object $cpt1 First post type object.
	 * @param object $cpt2 Second post type object.
	 *
	 * @return int
	 */
	private function sort_cpts_by_label( $cpt1, $cpt2 ) {

		return strcasecmp( $cpt1->labels->name, $cpt2->labels->name );
	}

	/**
	 * Get all translatable custom post types.
	 *
	 * @return array
	 */
	public function get_active_post_types() {

		$options = get_site_option( 'inpsyde_multilingual_cpt' );
		$out     = array();

		if ( empty( $options['post_types'] ) ) {
			return $out;
		}

		foreach ( $options['post_types'] as $post_type => $setting ) {
			if ( 0 !== (int) $setting ) {
				$out[] = $post_type;
			}
		}

		return array_unique( $out );
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
				<input type="checkbox" id="translate_this_post" name="translate_this_post"
					<?php
					/**
					 * Filter the default value of the 'Translate this post' checkbox.
					 *
					 * @param bool $translate Should 'Translate this post' be checked by default?
					 */
					$translate = (bool) apply_filters( 'mlp_translate_this_post_checkbox', false );
					checked( $translate );
					?>
				>
				<?php esc_html_e( 'Translate this post', 'multilingual-press' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * add the link filter to change to non permalinks
	 *
	 * @access  public
	 * @since   0.9
	 * @uses    add_filter
	 * @return  void
	 */
	public function before_mlp_link() {

		add_filter( 'post_type_link', array( $this, 'change_cpt_slug' ), 10, 2 );
	}

	/**
	 * remove the link filter to avoid replacing all permalinks
	 *
	 * @access  public
	 * @since   0.9
	 * @uses    remove_filter
	 * @return  void
	 */
	public function after_mlp_link() {

		remove_filter( 'post_type_link', array( $this, 'change_cpt_slug' ), 10 );
	}

	/**
	 * Change the permalink to ?posttype=<post-name> links to avoid problems
	 * when switch_to_blog and different rewrite_slugs on blogs.
	 *
	 * @param   string $post_link
	 * @param   WP_Post $post
	 * @return  string
	 */
	public function change_cpt_slug( $post_link, $post ) {

		if ( ! $this->is_cpt_with_dynamic_permalink( $post->post_type ) ) {
			return $post_link;
		}

		$draft_or_pending = $this->is_draft_or_pending( $post );
		$post_type        = get_post_type_object( $post->post_type );

		if ( $post_type->query_var && ( isset( $post->post_status ) && ! $draft_or_pending ) ) {
			$post_link = add_query_arg( $post_type->query_var, $post->post_name, '' );
		} else {
			$post_link = add_query_arg( array(
				'post_type' => $post->post_type,
				'p'         => $post->ID,
			), '' );
		}

		return home_url( $post_link );
	}

	/**
	 * Should this permalink be sent as a parameter?
	 *
	 * @param  string $post_type
	 * @return bool
	 */
	private function is_cpt_with_dynamic_permalink( $post_type ) {

		$options = get_site_option( 'inpsyde_multilingual_cpt' );

		if ( empty( $options ) ) {
			return false;
		}

		if ( empty( $options['post_types'] ) ) {
			return false;
		}

		if ( empty( $options['post_types'][ $post_type ] ) ) {
			return false;
		}

		return (int) $options['post_types'][ $post_type ] > 1;
	}

	/**
	 * Get post type status.
	 *
	 * @param  WP_Post $post
	 * @return bool
	 */
	public function is_draft_or_pending( $post ) {

		if ( empty( $post->post_status ) ) {
			return false;
		}

		return in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ), true );
	}
}
