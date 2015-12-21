<?php # -*- coding: utf-8 -*-

/**
 * Displays an element link flyout tab in the frontend.
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

		$this->nonce_validator = Mlp_Nonce_Validator_Factory::create( 'save_quicklink_position' );

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
	 * @param string $name
	 *
	 * @return mixed|void Either a value, or void for actions.
	 */
	public function update( $name ) {

	}

	/**
	 * @return bool
	 */
	private function register_setting() {

		return $this->module_manager->register(
			array(
				'description'  => __( 'Show link to translations in post content.', 'multilingual-press' ),
				'display_name' => __( 'Quicklink', 'multilingual-press' ),
				'slug'         => 'class-' . __CLASS__,
				'state'        => 'off',
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
	 * Create the tab and prepend it to the body tag.
	 *
	 * @wp-hook the_content
	 *
	 * @param string $content HTML content.
	 *
	 * @return string
	 */
	public function frontend_tab( $content ) {

		$translations = $this->get_translations();
		if ( empty( $translations ) ) {
			return $content;
		}

		$current_blog_id = get_current_blog_id();

		$translated = array();

		/** @var Mlp_Translation_Interface $translation */
		foreach ( $translations as $site => $translation ) {
			if ( $current_blog_id === $site ) {
				continue;
			}

			$translated[ $translation->get_remote_url() ] = $translation->get_language();
		}

		// Get post link option
		$option = get_site_option( 'inpsyde_multilingual_quicklink_options' );
		$position = isset( $option[ 'mlp_quicklink_position' ] ) ? $option[ 'mlp_quicklink_position' ] : 'tr';

		$switcher = $this->to_html( $translated, $position );

		// Position at the top
		if ( 't' === $position[ 0 ] ) {
			return $switcher . $content;
		}

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
	 * @param Mlp_Language[] $translated Translated posts.
	 * @param string         $position   Position of the quicklinks tab.
	 *
	 * @return string
	 */
	protected function to_html( array $translated, $position ) {

		if ( 4 > count( $translated ) ) {
			$type = 'links';
			$element = 'a';
			$glue = '<br>';
			$container = 'links';
		} else {
			$type = 'options';
			$element = 'option';
			$glue = '';
			$container = 'form';
		}

		$elements = array();

		foreach ( $translated as $url => $language ) {
			$attributes = array();

			if ( 'links' === $type ) {
				$attributes[ 'href' ] = $url;
				$attributes[ 'hreflang' ] = $language->get_name( 'http' );
				$attributes[ 'rel' ] = 'alternate';
			} else {
				$attributes[ 'value' ] = $url;
			}

			$attributes_html = '';
			foreach ( $attributes as $key => $value ) {
				$attributes_html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}

			$elements[ ] = sprintf(
				'<%1$s%2$s>%3$s</%1$s>',
				$element,
				$attributes_html,
				$language->get_name( 'native' )
			);
		}

		$html = implode( $glue, $elements );

		return $this->get_html_container( $html, $container, $translated, $position );
	}

	/**
	 * Returns the remote post links in form of up to three link elements, or a select element for more than three
	 * links.
	 *
	 * @param  string $selections 'option' or 'a' elements.
	 * @param  string $type       'links' or 'form'.
	 * @param  array  $translated Original array of translated posts, passed to the filter.
	 * @param  string $position   Quicklink position.
	 *
	 * @return string
	 */
	protected function get_html_container( $selections, $type, $translated, $position ) {

		$class_inner = 'mlp_inner';

		$label_text = esc_html_x( 'Read in:', 'Quicklink label', 'multilingual-press' );

		if ( 'links' === $type ) {
			$html = <<<HTML
<div class="$position mlp_quicklinks mlp_quicklinks_links">
	<div class="$class_inner">
		$label_text<br>
		$selections
	</div>
</div>
HTML;
		} else {
			$home_url = home_url();
			$home_url = esc_attr( $home_url );

			$select_id   = 'mlp-quicklink-select';
			$select_name = 'mlp_quicklink_select';

			$submit_text = esc_attr_x( 'Go', 'quicklink submit button', 'multilingual-press' );

			$html = <<<HTML
<form action="$home_url" method="post" class="$position mlp_quicklinks mlp_quicklinks_form" id="mlp-quicklink-form">
	<div class="$class_inner">
		<label for="$select_id">
			$label_text<br>
			<select name="$select_name" id="$select_id" autocomplete="off">
				$selections
			</select>
			<input type="submit" value="$submit_text">
		</label>
	</div>
</form>
HTML;

			wp_enqueue_script( 'mlp-frontend' );
		}

		// Positioned at the bottom.
		if ( 'b' === $position[0] ) {
			$html .= '<br class="mlp_clear">';
		}

		/**
		 * Filters the quicklink HTML.
		 *
		 * @param string $html       HTML output.
		 * @param string $type       Quicklink type, 'links' or 'form'.
		 * @param array  $translated Array of translated posts.
		 * @param string $selections Selections, 'option' or 'a' elements.
		 * @param string $position   Quicklink position.
		 *
		 * @return string
		 */
		return (string) apply_filters(
			'mlp_quicklinks_html',
			$html,
			$type,
			$translated,
			$selections,
			$position
		);
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

		if ( ! $this->nonce_validator->is_valid() ) {
			return;
		}

		// Get current site options
		$options = get_site_option( 'inpsyde_multilingual_quicklink_options' );

		// Get values from submitted form
		$options[ 'mlp_quicklink_position' ] = ( isset( $_POST[ 'quicklink-position' ] ) ) ? esc_attr( $_POST[ 'quicklink-position' ] ) : FALSE;

		update_site_option( 'inpsyde_multilingual_quicklink_options', $options );
	}
}
