<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\CustomPostTypeSupport;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

/**
 * Post type support settings updater.
 *
 * @package Inpsyde\MultilingualPress\Module\CustomPostTypeSupport
 * @since   3.0.0
 */
class PostTypeSupportSettingsUpdater {

	/**
	 * Settings name for all post type support input fields.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SETTINGS_NAME = 'mlp_cpt_support';

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var PostTypeRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param PostTypeRepository $repository Post type repository object.
	 * @param Nonce              $nonce      Nonce object.
	 */
	public function __construct( PostTypeRepository $repository, Nonce $nonce ) {

		$this->repository = $repository;

		$this->nonce = $nonce;
	}

	/**
	 * Updates the post type support settings.
	 *
	 * @since   3.0.0
	 * @wp-hook multilingualpress.save_modules
	 *
	 * @param array $data Request data.
	 *
	 * @return bool Whether or not the settings were updated successfully.
	 */
	public function update_settings( array $data ): bool {

		if ( ! $this->nonce->is_valid() ) {
			return false;
		}

		$custom_post_types = $this->repository->get_custom_post_types();

		if ( ! $custom_post_types || empty( $data[ static::SETTINGS_NAME ] ) ) {
			return $this->repository->unset_supported_post_types();
		}

		$custom_post_types = array_keys( $custom_post_types );

		$settings = (array) $data[ static::SETTINGS_NAME ];

		$custom_post_types = array_combine( $custom_post_types, array_map( function ( $slug ) use ( $settings ) {

			if ( empty( $settings[ $slug ] ) ) {
				return PostTypeRepository::CPT_INACTIVE;
			}

			return empty( $settings["{$slug}|links"] )
				? PostTypeRepository::CPT_ACTIVE
				: PostTypeRepository::CPT_QUERY_BASED;
		}, $custom_post_types ) );

		return $this->repository->set_supported_post_types( $custom_post_types );
	}
}
