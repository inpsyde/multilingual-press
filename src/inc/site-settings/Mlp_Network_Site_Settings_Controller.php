<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Admin\AdminNotice;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Type\Setting;
use Inpsyde\MultilingualPress\MultilingualPress;

/**
 * Class Mlp_Network_Site_Settings_Controller
 *
 * Handle settings for the whole network.
 *
 * @version 2014.01.15
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings_Controller implements Mlp_Updatable {

	/**
	 * @var Setting
	 */
	private $setting;

	/**
	 * @var Mlp_Network_Site_Settings_Properties
	 */
	private $page_properties;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @wp-hook plugins_loaded
	 *
	 * @param Setting $setting Setting object.
	 * @param Nonce   $nonce   Nonce object.
	 */
	public function __construct( Setting $setting, Nonce $nonce ) {

		$this->setting = $setting;

		$this->nonce = $nonce;

		add_action( 'admin_post_' . $setting->action(), [ $this, 'update_settings' ] );

		$this->page_properties = new Mlp_Network_Site_Settings_Properties();

		add_action( 'admin_print_styles-' . $this->page_properties->get_param_value(), function () {

			MultilingualPress::resolve( 'multilingualpress.asset_manager' )->enqueue_style( 'multilingualpress-admin' );
		} );

		new Mlp_Network_Site_Settings( $this->page_properties, $this );
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|void Either a value, or void for actions.
	 */
	public function update( $name ) {

		if ( 'create_tab_content' === $name ) {
			$this->show_update_message();

			$view = new Mlp_Network_Site_Settings_Tab_Content(
				$this->setting,
				$this->get_blog_id(),
				$this->nonce
			);
			$view->render_content();
		}
	}

	/**
	 * Combine all update actions.
	 *
	 * @return void
	 */
	public function update_settings() {

		if ( ! \Inpsyde\MultilingualPress\check_admin_referer( $this->nonce ) ) {
			wp_die( 'Invalid', 'Invalid', 403 );
		}

		$blog_id = $this->get_blog_id();

		// TODO: Call update routines for language, alternative language title, flag, and relationships - NOT wplang.

		/**
		 * Runs after having saved the site settings.
		 *
		 * @param array $data    The data to be saved.
		 * @param int   $site_id Site ID.
		 */
		do_action( 'mlp_blogs_save_fields', $_POST, $blog_id );

		wp_safe_redirect( add_query_arg( 'msg', 'updated', $_POST[ '_wp_http_referer' ] ) );
		\Inpsyde\MultilingualPress\call_exit();
	}

	/**
	 * @return int
	 */
	private function get_blog_id() {

		if ( empty ( $_REQUEST[ 'id' ] ) )
			return get_current_blog_id();

		return (int) $_REQUEST[ 'id' ];
	}

	/**
	 * Admin notices.
	 *
	 * @return void
	 */
	private function show_update_message() {

		if ( empty ( $_GET[ 'msg' ] ) or 'updated' !== $_GET[ 'msg' ] )
			return;

		( new AdminNotice( '<p>' . __( 'Settings saved.', 'multilingual-press' ) . '</p>' ) )->render();
	}
}
