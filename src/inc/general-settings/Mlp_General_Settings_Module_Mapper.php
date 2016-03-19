<?php # -*- coding: utf-8 -*-

/**
 * Class Mlp_General_Settings_Module_Mapper
 *
 * @version 2014.07.17
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_General_Settings_Module_Mapper implements Mlp_Module_Mapper_Interface {

	/**
	 * @var Mlp_Module_Manager_Interface
	 */
	private $modules;

	/**
	 * @var string
	 */
	private $nonce_action = 'mlp_modules';

	/**
	 * @param Mlp_Module_Manager_Interface $modules
	 */
	public function __construct( Mlp_Module_Manager_Interface $modules ) {

		$this->modules = $modules;
	}

	/**
	 * Save module options.
	 *
	 * @return    void
	 */
	public function update_modules() {

		check_admin_referer( $this->nonce_action );

		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die( 'FU' );
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

		// backwards compatibility
		if ( has_action( 'mlp_settings_save_fields' ) ) {
			_doing_it_wrong(
				'mlp_settings_save_fields',
				'mlp_settings_save_fields is deprecated, use mlp_modules_save_fields instead.',
				'1.2'
			);

			/**
			 * @see mlp_modules_save_fields
			 * @deprecated
			 */
			do_action( 'mlp_settings_save_fields' );
		}

		wp_safe_redirect( network_admin_url( 'settings.php?page=mlp&message=updated' ) );
		mlp_exit();
	}

	/**
	 *
	 * @return void
	 */
	private function set_module_activation_status() {

		$modules = $this->modules->get_modules();
		$slugs = array_keys( $modules );

		foreach ( $slugs as $slug ) {
			if ( isset( $_POST[ "mlp_state_$slug" ] ) && '1' === $_POST[ "mlp_state_$slug" ] ) {
				$this->modules->activate( $slug );
			} else {
				$this->modules->deactivate( $slug );
			}
		}

		$this->modules->save();
	}

	/**
	 * @param string $status
	 *
	 * @return array
	 */
	public function get_modules( $status = 'all' ) {

		return $this->modules->get_modules( $status );
	}

	/**
	 *
	 * @return string
	 */
	public function get_nonce_action() {

		return $this->nonce_action;
	}

}
