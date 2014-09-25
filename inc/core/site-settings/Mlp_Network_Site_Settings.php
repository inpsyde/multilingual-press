<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Network_Site_Settings
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings {

	/**
	 * @var array
	 */
	private $targets = array (
		'site-info.php',
		'site-users.php',
		'site-themes.php',
		'site-settings.php'
	);

	/**
	 * HTML comment to identify the page content.
	 *
	 * @see reorder_output()
	 * @type string
	 */
	private $marker;

	/**
	 * Tab configuration.
	 *
	 * @type Inpsyde_Property_List_Interface
	 */
	private $config;

	/**
	 * Updatable object. Currently used for tab content only.
	 *
	 * @type Mlp_Updatable
	 */
	private $watcher;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Network_Site_Settings_Properties $config
	 * @param Mlp_Updatable $watcher
	 */
	public function __construct(
		Mlp_Network_Site_Settings_Properties  $config,
		Mlp_Updatable                         $watcher
	)
	{
		global $pagenow;

		$this->config    = $config;
		$this->watcher   = $watcher;
		$this->targets[] = $config->get_param_value();
		$this->marker    = "<!--" . $config->get_param_value() . "-->";

		$this->set_pagenow();

		if ( ! empty ( $pagenow ) and in_array ( $pagenow, $this->targets ) )
			add_action( 'network_admin_notices', array ( $this, 'start_buffer' ) );
	}

	/**
	 * Start output buffering and call "render_callback".
	 *
	 * @return void
	 */
	public function start_buffer() {

		ob_start( array ( $this, 'reorder_output' ) );

		if ( ! $this->is_active_page() )
			return;

		print $this->marker;
		$this->watcher->update( 'create_tab_content' );
		print $this->marker;
	}

	/**
	 * Change the page content, adds navigation tab.
	 *
	 * @param  string $content Complete content of the page
	 *                         'wp-admin/network/site-settings.php'
	 * @return string
	 */
	public function reorder_output( $content ) {

		// Our extra tab.
		$link = $this->get_nav_link();
		$page = '';

		if ( $this->is_active_page() ) {
			$marked  = explode( $this->marker, $content, 3 );
			$page    = $marked[ 1 ];
			$content = $marked[ 0 ] . $marked[ 2 ];
		}

		$parts = explode( '</h3>', $content, 2 );
		$nav   = $parts[ 0 ] . $link . '</h3>';

		if ( ! $this->is_active_page() )
			return $nav . $parts[ 1 ];

		$form  = explode( '</form>', $parts[ 1 ], 2 );

		return $nav . $page . $form[ 1 ];
	}

	/**
	 * Check if global pagenow matches the tab identifier.
	 *
	 * @return boolean
	 */
	private function is_active_page() {

		return $GLOBALS[ 'pagenow' ] === $this->config->get_param_value();
	}

	/**
	 * Creates HTML for the navigation link.
	 *
	 * @return string
	 */
	private function get_nav_link() {

		$active  = $this->is_active_page() ? ' nav-tab-active' : '';
		$site_id = empty ( $_GET[ 'id' ] ) ? SITE_ID_CURRENT_SITE : (int) $_GET[ 'id' ];
		$name    = $this->config->get_param_name();
		$value   = $this->config->get_param_value();
		$url     = "site-settings.php?id=$site_id&amp;$name=$value";

		return sprintf(
			'<a href="%1$s" class="nav-tab%2$s" id="%3$s">%4$s</a>',
			$url,
			$active,
			$this->config->get_tab_id(),
			$this->config->get_tab_title()
		);
	}

	/**
	 * Changes the global variable to prevent wrong classes on the tab navigation.
	 *
	 * @return void
	 */
	private function set_pagenow() {

		$name  = $this->config->get_param_name();
		$value = $this->config->get_param_value();

		if ( ! isset ( $_GET[ $name ] ) )
			return;

		if ( $value !== $_GET[ $name ] )
			return;

		$GLOBALS[ 'pagenow' ] = $value;
	}
}