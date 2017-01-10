<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Setting\User\UserSettingViewModel;
use WP_User;

/**
 * Redirect user setting.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class RedirectUserSetting implements UserSettingViewModel {

	/**
	 * @var string
	 */
	private $meta_key;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var SettingsRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string             $meta_key   User meta key.
	 * @param Nonce              $nonce      Nonce object.
	 * @param SettingsRepository $repository LSettings repository object.
	 */
	public function __construct( $meta_key, Nonce $nonce, SettingsRepository $repository ) {

		$this->meta_key = (string) $meta_key;

		$this->nonce = $nonce;

		$this->repository = $repository;
	}

	/**
	 * Returns the markup for the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_User $user User object.
	 *
	 * @return string The markup for the user setting.
	 */
	public function markup( WP_User $user ) {

		return sprintf(
			'<label for="%2$s"><input type="checkbox" name="%2$s" value="1" id="%2$s"%3$s>%1$s</label>%4$s',
			esc_html__( 'Do not redirect me to the best matching language version.', 'multilingual-press' ),
			esc_attr( $this->meta_key ),
			checked( $this->repository->get_user_setting( $user->ID ), true, false ),
			\Inpsyde\MultilingualPress\nonce_field( $this->nonce )
		);
	}

	/**
	 * Returns the title of the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_User $user User object.
	 *
	 * @return string The markup for the user setting.
	 */
	public function title( WP_User $user ) {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Redirect', 'multilingual-press' ),
			esc_attr( $this->meta_key )
		);
	}
}
