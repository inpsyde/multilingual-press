<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Core\TaxonomyRepository;

/**
 * Taxonomy settings updater.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
class TaxonomySettingsUpdater {

	/**
	 * Settings name for all taxonomy support input fields.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SETTINGS_NAME = 'taxonomy_settings';

	/**
	 * Settings field name for all taxonomy support input fields.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SETTINGS_FIELD_ACTIVE = 'active';

	/**
	 * Settings field name for all taxonomy support input fields.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SETTINGS_FIELD_UI = 'ui';

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var TaxonomyRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TaxonomyRepository $repository Taxonomy repository object.
	 * @param Nonce              $nonce      Nonce object.
	 */
	public function __construct( TaxonomyRepository $repository, Nonce $nonce ) {

		$this->repository = $repository;

		$this->nonce = $nonce;
	}

	/**
	 * Updates the taxonomy settings.
	 *
	 * @since 3.0.0
	 *
	 * @param Request $request HTTP request object.
	 *
	 * @return bool Whether or not the settings were updated successfully.
	 */
	public function update_settings( Request $request ): bool {

		if ( ! $this->nonce->is_valid() ) {
			return false;
		}

		$available_taxonomies = $this->repository->get_available_taxonomies();

		$settings = (array) $request->body_value(
			static::SETTINGS_NAME,
			INPUT_POST,
			FILTER_DEFAULT,
			FILTER_FORCE_ARRAY
		);

		if ( ! $available_taxonomies || ! $settings ) {
			return $this->repository->unset_supported_taxonomies();
		}

		$available_taxonomies = array_keys( $available_taxonomies );
		$available_taxonomies = array_combine( $available_taxonomies, array_map( function ( $slug ) use ( $settings ) {

			if ( empty( $settings[ $slug ][ self::SETTINGS_FIELD_ACTIVE ] ) ) {
				return [
					TaxonomyRepository::FIELD_ACTIVE => false,
					TaxonomyRepository::FIELD_UI     => '',
				];
			}

			return [
				TaxonomyRepository::FIELD_ACTIVE => true,
				TaxonomyRepository::FIELD_UI     => (string) ( $settings[ $slug ][ self::SETTINGS_FIELD_UI ] ?? '' ),
			];
		}, $available_taxonomies ) );

		return $this->repository->set_supported_taxonomies( $available_taxonomies );
	}
}
