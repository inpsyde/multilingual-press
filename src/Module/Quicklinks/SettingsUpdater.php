<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Quicklinks;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

/**
 * Quicklinks settings updater.
 *
 * @package Inpsyde\MultilingualPress\Module\Quicklinks
 * @since   3.0.0
 */
class SettingsUpdater {

	/**
	 * Settings name for all quicklinks input fields.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SETTINGS_NAME = 'mlp_quicklinks';

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
	 * @param SettingsRepository $repository Settings repository object.
	 * @param Nonce              $nonce      Nonce object.
	 */
	public function __construct( SettingsRepository $repository, Nonce $nonce ) {

		$this->repository = $repository;

		$this->nonce = $nonce;
	}

	/**
	 * Updates the quicklinks settings.
	 *
	 * @since   3.0.0
	 * @wp-hook multilingualpress.save_modules
	 *
	 * @param Request $request Request data.
	 *
	 * @return bool Whether or not the settings were updated successfully.
	 */
	public function update_settings( Request $request ): bool {

		if ( ! $this->nonce->is_valid() ) {
			return false;
		}

		$setting = $request->body_value( static::SETTINGS_NAME, INPUT_POST, FILTER_DEFAULT, FILTER_FORCE_ARRAY );

		if ( empty( $setting ) ) {
			return false;
		}

		if ( ! empty( $setting['position'] ) ) {
			return $this->repository->set_position( $setting['position'] );
		}

		return false;
	}
}
