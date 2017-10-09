<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Setting\User\UserSettingViewModel;

use function Inpsyde\MultilingualPress\nonce_field;

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
	 * @param SettingsRepository $repository Settings repository object.
	 */
	public function __construct( string $meta_key, Nonce $nonce, SettingsRepository $repository ) {

		$this->meta_key = $meta_key;

		$this->nonce = $nonce;

		$this->repository = $repository;
	}

	/**
	 * Returns the markup for the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_User $user User object.
	 *
	 * @return string The markup for the user setting.
	 */
	public function markup( \WP_User $user ): string {

		return sprintf(
			'<label for="%2$s"><input type="checkbox" name="%2$s" value="1" id="%2$s"%3$s>%1$s</label>%4$s',
			esc_html__( 'Do not redirect me to the best matching language version.', 'multilingualpress' ),
			esc_attr( $this->meta_key ),
			checked( $this->repository->get_user_setting( (int) $user->ID ), true, false ),
			nonce_field( $this->nonce )
		);
	}

	/**
	 * Returns the title of the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the user setting.
	 */
	public function title(): string {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Redirect', 'multilingualpress' ),
			esc_attr( $this->meta_key )
		);
	}
}
