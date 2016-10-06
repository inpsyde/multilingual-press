<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\User;

use WP_User;

/**
 * User setting.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\User
 * @since   3.0.0
 */
class UserSetting {

	/**
	 * @var bool
	 */
	private $check_user;

	/**
	 * @var UserSettingViewModel
	 */
	private $model;

	/**
	 * @var UserSettingUpdater
	 */
	private $updater;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param UserSettingViewModel $model      View model object.
	 * @param UserSettingUpdater   $updater    Updater object.
	 * @param bool                 $check_user Optional. Only render for users capable of editing? Defaults to true.
	 */
	public function __construct( UserSettingViewModel $model, UserSettingUpdater $updater, $check_user = true ) {

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

		add_action( 'personal_options', function ( WP_User $user ) {

			( new UserSettingView( $this->model, $this->check_user ) )->render( $user );
		} );

		add_action( 'profile_update', [ $this->updater, 'update' ] );
	}
}
