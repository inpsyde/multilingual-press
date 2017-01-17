<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Quicklinks;

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
	 * @param array $data Request data.
	 *
	 * @return bool Whether or not the settings were updated successfully.
	 */
	public function update_settings( array $data ) {

		if ( ! $this->nonce->is_valid() ) {
			return false;
		}

		if ( empty( $data[ static::SETTINGS_NAME ] ) ) {
			return false;
		}

		if ( ! empty( $data[ static::SETTINGS_NAME ]['position'] ) ) {
			return $this->repository->set_position( $data[ static::SETTINGS_NAME ]['position'] );
		}

		return false;
	}
}
