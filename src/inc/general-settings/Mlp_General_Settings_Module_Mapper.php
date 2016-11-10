<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;

/**
 * Class Mlp_General_Settings_Module_Mapper
 *
 * @version 2014.07.17
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_General_Settings_Module_Mapper implements Mlp_Module_Mapper_Interface {

	/**
	 * @var ModuleManager
	 */
	private $module_manager;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @param ModuleManager $module_manager
	 * @param Nonce         $nonce          Nonce object.
	 */
	public function __construct( ModuleManager $module_manager, Nonce $nonce ) {

		$this->module_manager = $module_manager;

		$this->nonce = $nonce;
	}

	/**
	 * Save module options.
	 *
	 * @return    void
	 */
	public function update_modules() {

		\Inpsyde\MultilingualPress\check_admin_referer( $this->nonce );

		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die();
		}

		$this->set_module_activation_status();

		/**
		 * Runs before the redirect.
		 *
		 * Process your fields in the $_POST superglobal here and then call update_site_option().
		 *
		 * @param array $_POST
		 */
		do_action( 'mlp_modules_save_fields', $_POST );

		wp_safe_redirect( network_admin_url( 'settings.php?page=mlp&message=updated' ) );
		\Inpsyde\MultilingualPress\call_exit();
	}

	/**
	 *
	 * @return void
	 */
	private function set_module_activation_status() {

		$modules = $this->module_manager->get_modules();

		$ids = array_keys( $modules );

		foreach ( $ids as $id ) {
			if ( isset( $_POST[ "mlp_state_$id" ] ) && '1' === $_POST[ "mlp_state_$id" ] ) {
				$this->module_manager->activate_module( $id );
			} else {
				$this->module_manager->deactivate_module( $id );
			}
		}

		$this->module_manager->save_modules();
	}

	/**
	 * @param int $state
	 *
	 * @return Module[]
	 */
	public function get_modules( $state = ModuleManager::MODULE_STATE_ALL ) {

		return $this->module_manager->get_modules( $state );
	}
}
