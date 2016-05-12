<?php # -*- coding: utf-8 -*-

/**
 * Displays an element link flyout tab in the frontend.
 */
class Mlp_Quicklink implements Mlp_Updatable {

	/**
	 * @var Mlp_Assets_Interface
	 */
	private $assets;

	/**
	 * @var Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * @var Mlp_Module_Manager_Interface
	 */
	private $module_manager;

	/**
	 * @var Inpsyde_Nonce_Validator
	 */
	private $nonce_validator;

	/**
	 * @var Mlp_Translation[]
	 */
	private $translations = array();

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param Mlp_Module_Manager_Interface $module_manager Module manager object.
	 * @param Mlp_Language_Api_Interface   $language_api   Language API object.
	 * @param Mlp_Assets_Interface         $assets         Asset manager object.
	 */
	public function __construct(
		Mlp_Module_Manager_Interface $module_manager,
		Mlp_Language_Api_Interface $language_api,
		Mlp_Assets_Interface $assets
	) {

		$this->module_manager = $module_manager;

		$this->language_api = $language_api;

		$this->assets = $assets;

		$this->nonce_validator = Mlp_Nonce_Validator_Factory::create( 'save_quicklink_position' );
	}

	/**
	 * Wires up all functions.
	 *
	 * @return void
	 */
	public function initialize( ) {

		// Quit here if module is turned off
		if ( ! $this->register_setting() ) {
			return;
		}

		if ( is_admin() ) {
			add_action( 'mlp_modules_add_fields', array( $this, 'draw_options_page_form_fields' ) );

			// Use this hook to handle the user input of your modules' options page form fields
			add_filter( 'mlp_modules_save_fields', array( $this, 'save_options_page_form_fields' ) );
		} else {
			if ( ! empty( $_POST['mlp_quicklink_select'] ) ) {
				$this->redirect_quick_link( (string) $_POST['mlp_quicklink_select'] );
			}

			add_action( 'wp_head', array( $this, 'load_style' ), 0 );

			add_filter( 'the_content', array( $this, 'frontend_tab' ) );
		}
	}

	/**
	 * Requires the stylesheet.
	 *
	 * @return bool
	 */
	public function load_style() {

		$translations = $this->get_translations();
		if ( ! $translations ) {
			return false;
		}

		$theme_support = get_theme_support( 'multilingualpress' );
		if ( ! empty( $theme_support[0]['quicklink_style'] ) ) {
			return false;
		}

		return $this->assets->provide( 'mlp_frontend_css' );
	}

	/**
	 * Nothing to do here.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function update( $name ) {
	}

	/**
	 * Registers the module.
	 *
	 * @return bool
	 */
	private function register_setting() {

		return $this->module_manager->register( array(
			'description'  => __( 'Show link to translations in post content.', 'multilingual-press' ),
			'display_name' => __( 'Quicklink', 'multilingual-press' ),
			'slug'         => 'class-' . __CLASS__,
			'state'        => 'off',
		) );
	}

	/**
	 * Catches quicklink submissions and redirects if the URL is valid.
	 *
	 * @since 1.0.4
	 *
	 * @param string $url The URL that is to be redirected to.
	 *
	 * @return void
	 */
	private function redirect_quick_link( $url ) {

		$callback = array( $this, 'extend_allowed_hosts' );
		add_filter( 'allowed_redirect_hosts', $callback, 10, 2 );

		$url = wp_validate_redirect( $url, false );

		remove_filter( 'allowed_redirect_hosts', $callback );

		if ( ! $url ) {
			return;
		}

		// Force GET request.
		wp_redirect( $url, 303 );
		mlp_exit();
	}

	/**
	 * Adds all domains of the network to the allowed hosts.
	 *
	 * @wp-hook allowed_redirect_hosts
	 *
	 * @since 1.0.4
	 *
	 * @param string[] $home_hosts  Array with one entry: the host of home_url().
	 * @param string   $remote_host Host name of the URL to validate.
	 *
	 * @return string[]
	 */
	public function extend_allowed_hosts( array $home_hosts, $remote_host ) {

		// Network with sub directories.
		if ( in_array( $remote_host, $home_hosts, true ) ) {
			return $home_hosts;
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		$query = "
SELECT domain
FROM {$wpdb->blogs}
WHERE site_id = %d
	AND public   = '1'
	AND archived = '0'
	AND mature   = '0'
	AND spam     = '0'
	AND deleted  = '0'
ORDER BY domain DESC";
		$query = $wpdb->prepare( $query, $wpdb->siteid );

		$domains = $wpdb->get_col( $query );

		$allowed_hosts = array_merge( $home_hosts, $domains );
		$allowed_hosts = array_unique( $allowed_hosts );

		return $allowed_hosts;
	}

	/**
	 * Deletes the according site option on module deactivation.
	 *
	 * @since 0.1
	 *
	 * @return void
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

		/** @var Mlp_Translation_Interface[] $translations */
		$translations = $this->get_translations();
		if ( ! $translations ) {
			return $content;
		}

		$current_blog_id = get_current_blog_id();

		$translated = array();

		foreach ( $translations as $site => $translation ) {
			if ( $current_blog_id === $site ) {
				continue;
			}

			$translated[ $translation->get_remote_url() ] = $translation->get_language();
		}

		// Get post link option.
		$option = get_site_option( 'inpsyde_multilingual_quicklink_options' );

		$position = isset( $option['mlp_quicklink_position'] ) ? $option['mlp_quicklink_position'] : 'tr';

		$switcher = $this->to_html( $translated, $position );

		if ( 't' === $position[0] ) {
			// Position at the top.
			return $switcher . $content;
		}

		// Position at the bottom.
		return $content . $switcher;
	}

	/**
	 * Returns the translations.
	 *
	 * @return Mlp_Translation_Interface[]
	 */
	private function get_translations() {

		if ( ! is_singular() ) {
			return array();
		}

		if ( $this->translations ) {
			return $this->translations;
		}

		$this->translations = $this->language_api->get_translations( array( 'type' => 'post' ) );

		return $this->translations;
	}

	/**
	 * Converts the list of translated posts into HTML.
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
			if ( 'links' === $type ) {
				$attributes = array(
					'href'     => $url,
					'hreflang' => $language->get_name( 'http' ),
					'rel'      => 'alternate',
				);
			} else {
				$attributes = array(
					'value' => $url,
				);
			}

			$attributes_html = '';

			foreach ( $attributes as $key => $value ) {
				$attributes_html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}

			$elements[] = sprintf(
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
<div class="mlp-quicklinks mlp-quicklink-links $position mlp_quicklinks mlp_quicklinks_links">
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
<form action="$home_url" method="post" class="mlp-quicklinks mlp-quicklink-form $position mlp_quicklinks mlp_quicklinks_form">
	<div class="$class_inner">
		<label for="$select_id">
			$label_text
			<select name="$select_name" id="$select_id" autocomplete="off">
				$selections
			</select>
		</label>
		<input type="submit" value="$submit_text">
	</div>
</form>
HTML;

			wp_enqueue_script( 'mlp-frontend' );
		}

		/**
		 * Filters the quicklinks HTML.
		 *
		 * @param string $html       HTML output.
		 * @param string $type       Quicklink type, 'links' or 'form'.
		 * @param array  $translated Array of translated posts.
		 * @param string $selections Selections, 'option' or 'a' elements.
		 * @param string $position   Quicklink position.
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
	 * Displays the module options page form fields.
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function draw_options_page_form_fields() {

		$data = new Mlp_Quicklink_Positions_Data( $this->nonce_validator );

		$box = new Mlp_Extra_General_Settings_Box( $data );
		$box->print_box();
	}

	/**
	 * Saves module user input.
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function save_options_page_form_fields() {

		if ( ! $this->nonce_validator->is_valid() ) {
			return;
		}

		// Get current site options
		$options = get_site_option( 'inpsyde_multilingual_quicklink_options' );

		// Get values from submitted form
		$options['mlp_quicklink_position'] = isset( $_POST['quicklink-position'] )
			? esc_attr( $_POST['quicklink-position'] )
			: false;

		update_site_option( 'inpsyde_multilingual_quicklink_options', $options );
	}
}
