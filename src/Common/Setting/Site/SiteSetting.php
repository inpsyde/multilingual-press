<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\Site;

/**
 * Site setting.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\Site
 * @since   3.0.0
 */
class SiteSetting {

	/**
	 * @var bool
	 */
	private $check_user;

	/**
	 * @var SiteSettingViewModel
	 */
	private $model;

	/**
	 * @var SiteSettingUpdater
	 */
	private $updater;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingViewModel $model      View model object.
	 * @param SiteSettingUpdater   $updater    Updater object.
	 * @param bool                 $check_user Optional. Only render for users capable of editing? Defaults to true.
	 */
	public function __construct( SiteSettingViewModel $model, SiteSettingUpdater $updater, $check_user = true ) {

		$this->model = $model;

		$this->updater = $updater;

		$this->check_user = (bool) $check_user;
	}

	/**
	 * Registers the according callbacks.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register() {

		// TODO: Adapt hook as soon as it is a class constant (see Mlp_Network_Site_Settings_Tab_Content).
		add_action( 'mlp_blogs_add_fields', function ( $site_id ) {

			( new SiteSettingView( $this->model, $this->check_user ) )->render( $site_id );
		} );

		// TODO: Adapt hook as soon as it is a class constant (see Mlp_Network_Site_Settings_Controller).
		add_action( 'mlp_blogs_save_fields', [ $this->updater, 'update' ], 10, 2 );
	}
}
