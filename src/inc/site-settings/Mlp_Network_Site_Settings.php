<?php # -*- coding: utf-8 -*-

/**
 * Class Mlp_Network_Site_Settings
 */
class Mlp_Network_Site_Settings {

	/**
	 * Tab configuration.
	 *
	 * @var Inpsyde_Property_List_Interface
	 */
	private $config;

	/**
	 * HTML comment to identify the page content.
	 *
	 * @see reorder_output()
	 * @var string
	 */
	private $marker;

	/**
	 * @var string[]
	 */
	private $targets = array(
		'site-info.php',
		'site-users.php',
		'site-themes.php',
		'site-settings.php',
	);

	/**
	 * Updatable object. Currently used for tab content only.
	 *
	 * @var Mlp_Updatable
	 */
	private $watcher;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Network_Site_Settings_Properties $config
	 * @param Mlp_Updatable                        $watcher
	 */
	public function __construct( Mlp_Network_Site_Settings_Properties $config, Mlp_Updatable $watcher ) {

		global $pagenow;

		$this->config = $config;

		$this->marker = '<!-- ' . esc_html( $config->get_param_value() ) . ' -->';

		$this->targets[] = $config->get_param_value();

		$this->watcher = $watcher;

		$this->set_pagenow();

		if ( ! empty( $pagenow ) && in_array( $pagenow, $this->targets, true ) ) {
			add_action( 'network_admin_notices', array( $this, 'start_buffer' ) );
		}
	}

	/**
	 * Starts output buffering and call "render_callback".
	 *
	 * @return void
	 */
	public function start_buffer() {

		ob_start( array( $this, 'reorder_output' ) );

		if ( ! $this->is_active_page() ) {
			return;
		}

		// @codingStandardsIgnoreLine as rendering an HTML comment is not possible with esc_html() or wp_kses_*().
		echo $this->marker;

		$this->watcher->update( 'create_tab_content' );

		// @codingStandardsIgnoreLine as rendering an HTML comment is not possible with esc_html() or wp_kses_*().
		echo $this->marker;
	}

	/**
	 * Changes the page content, adds navigation tab.
	 *
	 * @param string $content Complete content of the page 'wp-admin/network/site-settings.php'.
	 *
	 * @return string
	 */
	public function reorder_output( $content ) {

		// Our extra tab.
		$link = $this->get_nav_link();

		$page = '';

		if ( $this->is_active_page() ) {
			$marked = explode( $this->marker, $content, 3 );

			$page = $marked[1];

			$content = str_replace( 'nav-tab-active', '', $marked[0] . $marked[2] );
		}

		$closing_tag = '</' . $this->get_heading_level() . '>';

		$parts = explode( $closing_tag, $content, 2 );

		$nav = $parts[0] . $link . $closing_tag;

		if ( ! $this->is_active_page() ) {
			return $nav . $parts[1];
		}

		$form = explode( '</form>', $parts[1], 2 );

		return $nav . $page . $form[1];
	}

	/**
	 * Returns the heading level wrt. the current WordPress version.
	 *
	 * @return string
	 */
	private function get_heading_level() {

		// Get the unaltered WordPress version.
		require ABSPATH . WPINC . '/version.php';

		/** @var string $wp_version */
		$heading_level = version_compare( $wp_version, '4.4-alpha', '<' ) ? 'h3' : 'h2';

		return $heading_level;
	}

	/**
	 * Checks if the global pagenow matches the tab identifier.
	 *
	 * @return bool
	 */
	private function is_active_page() {

		return $GLOBALS['pagenow'] === $this->config->get_param_value();
	}

	/**
	 * Creates the HTML for the navigation link.
	 *
	 * @return string
	 */
	private function get_nav_link() {

		$site_id = absint( filter_input( INPUT_GET, 'id' ) );
		if ( ! $site_id ) {
			$site_id = SITE_ID_CURRENT_SITE;
		}

		$name = $this->config->get_param_name();

		$value = $this->config->get_param_value();

		$url = "site-settings.php?id=$site_id&amp;$name=$value";

		$active = $this->is_active_page() ? ' nav-tab-active' : '';

		return sprintf(
			'<a href="%1$s" class="nav-tab%2$s" id="%3$s">%4$s</a>',
			$url,
			$active,
			$this->config->get_tab_id(),
			$this->config->get_tab_title()
		);
	}

	/**
	 * Changes the global pagenow to prevent wrong classes on the tab navigation.
	 *
	 * @return void
	 */
	private function set_pagenow() {

		$name = $this->config->get_param_name();

		$pagenow = (string) filter_input( INPUT_GET, $name );
		if ( '' === $pagenow ) {
			return;
		}

		$value = $this->config->get_param_value();
		if ( $value !== $pagenow ) {
			return;
		}

		// @codingStandardsIgnoreLine as we really DO want to override the global.
		$GLOBALS['pagenow'] = $value;
	}
}
