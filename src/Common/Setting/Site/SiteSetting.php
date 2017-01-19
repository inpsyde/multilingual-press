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
	 * @param string $render_hook Action hook for rendering to be triggered.
	 * @param string $update_hook Optional. Action hook for updating to be triggered. Defaults to empty string.
	 *
	 * @return void
	 */
	public function register( $render_hook, $update_hook = '' ) {

		add_action( $render_hook, function ( $site_id ) {

			( new SiteSettingSingleView( $this->model, $this->check_user ) )->render( $site_id );
		} );

		if ( $update_hook ) {
			add_action( $update_hook, [ $this->updater, 'update' ], 10, 2 );
		}
	}
}
