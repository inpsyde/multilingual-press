<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Core\PostTypeRepository;

/**
 * Post type settings updater.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
class PostTypeSettingsUpdater {

	/**
	 * Settings name for all post type support input fields.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SETTINGS_NAME = 'post_type_settings';

	/**
	 * Settings field name for all post type support input fields.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SETTINGS_FIELD_ACTIVE = 'active';

	/**
	 * Settings field name for all post type support input fields.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SETTINGS_FIELD_PERMALINKS = 'permalinks';

	/**
	 * Settings field name for all post type support input fields.
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
	 * Updates the post type settings.
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

		$available_post_types = $this->repository->get_available_post_types();

		$settings = (array) $request->body_value(
			static::SETTINGS_NAME,
			INPUT_POST,
			FILTER_DEFAULT,
			FILTER_FORCE_ARRAY
		);

		if ( ! $available_post_types || ! $settings ) {
			return $this->repository->unset_supported_post_types();
		}

		$available_post_types = array_keys( $available_post_types );
		$available_post_types = array_combine( $available_post_types, array_map( function ( $slug ) use ( $settings ) {

			if ( empty( $settings[ $slug ][ self::SETTINGS_FIELD_ACTIVE ] ) ) {
				return [
					PostTypeRepository::FIELD_ACTIVE    => false,
					PostTypeRepository::FIELD_PERMALINK => false,
					PostTypeRepository::FIELD_UI        => '',
				];
			}

			$settings = $settings[ $slug ];

			return [
				PostTypeRepository::FIELD_ACTIVE    => true,
				PostTypeRepository::FIELD_PERMALINK => (bool) ( $settings[ self::SETTINGS_FIELD_PERMALINKS ] ?? false ),
				PostTypeRepository::FIELD_UI        => (string) ( $settings[ self::SETTINGS_FIELD_UI ] ?? '' ),
			];
		}, $available_post_types ) );

		return $this->repository->set_supported_post_types( $available_post_types );
	}
}
