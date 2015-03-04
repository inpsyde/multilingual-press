<?php
/**
 * Module Name:	MultilingualPress Quicklink Module
 * Description:	Display an element link flyout tab in the frontend
 * Author:		Inpsyde GmbH
 * Version:		0.3
 * Author URI:	http://inpsyde.com
 */
class Mlp_Quicklink implements Mlp_Updatable {

	/**
	 * @type Inpsyde_Nonce_Validator
	 */
	private $nonce_validator;

	/**
	 * @type Mlp_Module_Manager_Interface
	 */
	private $module_manager;

	/**
	 * @type Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * Return value from Language_Api
	 *
	 * @type array
	 */
	private $translations = array();

	/**
	 * @type Mlp_Assets_Interface
	 */
	private $assets;

	/**
	 * Constructor
	 *
	 * @param Mlp_Module_Manager_Interface $module_manager
	 * @param Mlp_Language_Api_Interface   $language_api
	 * @param Mlp_Assets_Interface         $assets
	 */
	public function __construct(
		Mlp_Module_Manager_Interface $module_manager,
		Mlp_Language_Api_Interface   $language_api,
		Mlp_Assets_Interface         $assets
	) {

		$this->module_manager = $module_manager;
		$this->language_api   = $language_api;
		$this->assets         = $assets;

		// Quit here if module is turned off
		if ( ! $this->register_setting() )
			return;

		$this->nonce_validator = new Inpsyde_Nonce_Validator( 'cpt_translator' );

		$this->redirect_quick_link();

		add_action( 'wp_head', array( $this, 'load_style' ), 0 );
		add_filter( 'the_content', array( $this, 'frontend_tab' ) );

		add_action( 'mlp_modules_add_fields', array ( $this, 'draw_options_page_form_fields' ) );
		// Use this hook to handle the user input of your modules' options page form fields
		add_filter( 'mlp_modules_save_fields', array ( $this, 'save_options_page_form_fields' ) );
	}

	/**
	 * Require the stylesheet
	 *
	 * @return bool
	 */
	public function load_style() {

		$translations = $this->get_translations();

		if ( empty ( $translations ) )
			return FALSE;

		$theme_support = get_theme_support( 'multilingualpress' );

		if ( $theme_support && ! empty ( $theme_support[0][ 'quicklink_style' ] ) )
			return FALSE;

		$this->assets->provide( 'mlp_frontend_css' );

		return TRUE;
	}

	/**
	 * @param  string $name
	 * @return mixed|void  Either void for actions or a value.
	 */
	public function update( $name ) {

		//
	}

	/**
	 * @return bool
	 */
	private function register_setting() {

		$desc = __(
			'Show link to translations in post content.',
			'multilingualpress'
		);

		return $this->module_manager->register(
			array (
				'display_name'	=> __( 'Quicklink', 'multilingualpress' ),
				'slug'			=> 'class-' . __CLASS__,
				'description'   => $desc
			)
		);
	}

	/**
	 * catch quicklink submissions and redirect if the URL is valid.
	 *
	 * @since  1.0.4
	 * @return void
	 */
	protected function redirect_quick_link() {

		if ( ! isset ( $_POST['mlp_quicklink_select'] ) )
			return;

		add_filter(
			'allowed_redirect_hosts',
			array ( $this, 'extend_allowed_hosts' ),
			10,
			2
		);

		$url = wp_validate_redirect( $_POST['mlp_quicklink_select'], FALSE );

		remove_filter(
			'allowed_redirect_hosts',
			array ( $this, 'extend_allowed_hosts' )
		);

		if ( ! $url )
			return;

		// force GET request
		wp_redirect( $url, 303 );
		exit;
	}

	/**
	 * Add all domains of a network to allowed hosts.
	 *
	 * @wp-hook allowed_redirect_hosts Called in wp_validate_redirect()
	 * @since   1.0.4
	 * @param   array  $home_hosts  Array with one entry: the host of home_url()
	 * @param   string $remote_host Host name of the URL to validate
	 * @return  array
	 */
	public function extend_allowed_hosts( Array $home_hosts, $remote_host ) {

		// network with sub directories
		if ( in_array( $remote_host, $home_hosts ) )
			return $home_hosts;

		/** @var wpdb $wpdb */
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT domain
				FROM " . $wpdb->blogs . "
				WHERE site_id = %d
					AND public   = '1'
					AND archived = '0'
					AND mature   = '0'
					AND spam     = '0'
					AND deleted  = '0'
				ORDER BY domain DESC",
			$wpdb->siteid
		);

		$domains = $wpdb->get_col( $sql );
		$all     = array_merge( $home_hosts, $domains );

		return array_unique( $all );
	}

	/**
	 * Callback upon deactivation of module.
	 * In this case, we cleanup the site options.
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	delete_site_option
	 * @return	void
	 */
	public static function deactivate_module() {

		delete_site_option( 'inpsyde_multilingual_quicklink_options' );
	}

	/**
	 * Create the tab and prepend it to the body-tag
	 *
	 * @since	0.1
	 * @access	public
	 * @param	string $content
	 * @uses	is_single, is_page, get_site_option, mlp_get_linked_elements, mlp_get_available_languages
	 * 			__, plugins_url, esc_html
	 * @return	string $content
	 */
	public function frontend_tab( $content ) {

		$translations = $this->get_translations();

		if ( empty ( $translations ) )
			return $content;

		// Get post link option
		$option = get_site_option( 'inpsyde_multilingual_quicklink_options' );

		if ( ! $option )
			$option = array( 'mlp_quicklink_position' => 'tr' );

		$translated = array();

		/** @var Mlp_Translation_Interface $translation */
		foreach ( $translations as $site => $translation ) {

			if ( get_current_blog_id() === $site )
				continue;

			$url                = $translation->get_remote_url();
			$translated[ $url ] = $translation->get_language()->get_name( 'native' );
		}

		$position = $option[ 'mlp_quicklink_position' ];
		$switcher = $this->to_html( $translated, $position );

		// position at the top
		if ( 't' === $position[0] )
			return $switcher . $content;

		return $content . $switcher;
	}

	/**
	 * @return array
	 */
	private function get_translations() {

		if ( ! empty ( $this->translations ) )
			return $this->translations;

		if ( ! is_singular() )
			return array();

		$this->translations = $this->language_api->get_translations(
			array ( 'type' => 'post' )
		);

		return $this->translations;
	}

	/**
	 * Convert the list of translated posts into HTML.
	 *
	 * @since  1.0.4
	 * @param  array $translated
	 * @param  string $position
	 * @return string
	 */
	protected function to_html( Array $translated, $position ) {

		$translated_raw = $translated;

		if ( 4 > count( $translated ) ) {
			array_walk( $translated, array ( $this, 'walk_translations' ), 'links' );
			$selections = join( '<br />', $translated );
			return $this->get_html_container( $selections, 'links', $translated, $position );
		}

		array_walk( $translated, array ( $this, 'walk_translations' ), 'options' );

		$selections = join( '', $translated );
		return $this->get_html_container( $selections, 'form', $translated_raw, $position );

	}

	/**
	 * Convert list of translated posts into 'a' or 'option' elements.
	 *
	 * @since 1.0.4
	 * @param string $text
	 * @param string $url
	 * @param  string $type 'links' or 'options'.
	 */
	protected function walk_translations( &$text, $url, $type ) {

		if ( 'links' === $type )
			$text = "<a href='$url' rel='alternate'>$text</a>";
		else
			$text = "<option value='$url'>$text</a>";
	}

	/**
	 * Put list of translated posts into the fitting HTML container
	 *
	 * @since  1.0.4
	 * @param  string $selections 'option' or 'a' elements.
	 * @param  string $type 'links' or 'form'.
	 * @param  array $translated Original array of translated posts, passed to the filter.
	 * @param  string $position
	 * @return string
	 */
	protected function get_html_container( $selections, $type, $translated, $position ) {

		$class_inner = 'mlp_inner';
		$label_text  = esc_html( _x( 'Read in:', 'Quicklink label', 'multilingualpress' ) );

		if ( 'links' === $type ) {

			$html = "<div class='$position mlp_quicklinks mlp_quicklinks_links'>
				<div class='$class_inner'>
					$label_text<br />
					$selections
				</div>
			</div>";

		} else {

			$action      = esc_attr( home_url() );
			$select_name = 'mlp_quicklink_select';
			$go_text     = esc_attr_x( 'Go', 'quicklink submit button', 'multilingualpress' );
			$go_button   = '<input type="submit" value="' . $go_text . '">';
			$html = "<form method='post' class='$position mlp_quicklinks mlp_quicklinks_form' action='$action'>
				<div class='$class_inner'>
					<label for='{$select_name}_id'>$label_text<br />
					<select name='$select_name' id='{$select_name}_id'>
						$selections
					</select>
					$go_button
					</label>
				</div>
			</form>";

			add_action(
				'wp_print_footer_scripts',
				array ( $this, 'print_form_script' )
			);
		}

		// position at the bottom
		if ( 'b' === $position[0] )
			$html .= '<br class="clear" />';

		/**
		 * Return-Filter to Change the Quicklinks
		 * @param   String $html
		 * @param   String $type
		 * @param   Array  $translated
		 * @param   string $selections
		 * @param   String $position
		 */
		return apply_filters(
			'mlp_quicklinks_html',
			$html,
			$type,
			$translated,
			$selections,
			$position
		);
	}

	/**
	 * Enhance form submission to avoid extra WP processing.
	 *
	 * @since	1.0.4
	 */
	public function print_form_script() {
		?>
<script>
document.getElementById("mlp_quicklink_container").onsubmit = function() {
	this.method = 'get';
	var MLPselect = document.getElementById( "mlp_quicklink_select_id" );
	document.location.href = MLPselect.options[MLPselect.selectedIndex].value;
	return false;
};</script>
		<?php
	}

	/**
	 * Get the selected blog's post permalink
	 *
	 * @since	0.1
	 * @access	private
	 * @param	int $blog_id
	 * @param	int $post_id
	 * @uses	mlp_get_linked_elements, get_current_blog_id, get_blog_post, get_blog_permalink
	 * @return	string $permalink | the post permalink
	 */
	private function get_element_permalink( $blog_id, $post_id ) {

		// Get blog id of desired blog
		$remote_blog_id = intval( $blog_id );

		// Get all elements linked to the current one
		$elements = mlp_get_linked_elements( intval( $post_id ), '', get_current_blog_id() );

		// No linked elements found
		if ( array () == $elements || empty ( $elements[ $remote_blog_id ] ) )
			return '';

		$remote_post_id = intval( $elements[ $remote_blog_id ] );

		$post = get_blog_post( $remote_blog_id, $remote_post_id );

		if ( is_object( $post ) && 'publish' == $post->post_status )
			$permalink = get_blog_permalink( $remote_blog_id, $remote_post_id );
		else
			return '';

		if ( 1 < strlen( $permalink ) )
			return $permalink;

		return '';
	}

	/**
	 * This is the callback of the metabox used to display
	 * the modules options page form fields
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	get_site_option, _e
	 * @return	void
	 */
	public function draw_options_page_form_fields() {

		$data = new Mlp_Quicklink_Positions_Data( $this->nonce_validator );
		$box  = new Mlp_Extra_General_Settings_Box( $data );
		$box->print_box();
	}

	/**
	 * Hook into mlp_settings_save_fields to
	 * handle module user input
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	get_site_option, update_site_option, esc_attr
	 * @return	void
	 */
	public function save_options_page_form_fields() {

		// Get current site options
		$options = get_site_option( 'inpsyde_multilingual_quicklink_options' );

		// Get values from submitted form
		$options[ 'mlp_quicklink_position' ] = ( isset( $_POST[ 'quicklink-position' ] ) ) ? esc_attr( $_POST[ 'quicklink-position' ] ) : FALSE;

		update_site_option( 'inpsyde_multilingual_quicklink_options', $options );
	}
}