<?php # -*- coding: utf-8 -*-

class Mlp_General_Settings_Module_Mapper implements Mlp_Module_Mapper_Interface
{

	protected $modules;

	protected $nonce_action = 'mlp_modules';

	/**
	 * Constructor.
	 */
	public function __construct( Mlp_Module_Manager_Interface $modules )
	{

		$this->modules = $modules;
	}

	/**
	 * Save module options.
	 *
	 * @return    void
	 */
	public function update_modules()
	{

		check_admin_referer( $this->nonce_action );

		if ( ! current_user_can( 'manage_network_options' ) )
			wp_die( 'FU' );

		/**
		 * @var Mlp_Module_Manager
		 */
		$modules = $this->modules->get_modules();

		foreach ( $modules as $slug => $module ) {
			if ( isset ( $_POST[ "mlp_state_$slug" ] ) && '1' === $_POST[ "mlp_state_$slug" ] )
				$this->modules->activate( $slug );
			else
				$this->modules->deactivate( $slug );
		}

		$this->modules->save();

		// process your fields from $_POST here and update_site_option
		do_action( 'mlp_modules_save_fields', $_POST );

		// backwards compatibility
		if ( has_action( 'mlp_settings_save_fields' ) ) {
			_doing_it_wrong(
				'mlp_settings_save_fields',
				'mlp_settings_save_fields is deprecated, use mlp_modules_save_fields instead.',
				'1.2'
			);
		}
		do_action( 'mlp_settings_save_fields' );

		wp_safe_redirect( network_admin_url( 'settings.php?page=mlp&message=updated' ) );
		exit;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Mlp_Module_Mapper_Interface::get_modules()
	 */
	public function get_modules( $status = 'all' )
	{
		return $this->modules->get_modules( $status );
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Mlp_Module_Mapper_Interface::get_nonce_action()
	 */
	public function get_nonce_action()
	{
		return $this->nonce_action;
	}
}