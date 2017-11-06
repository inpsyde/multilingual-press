<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core;

/**
 * Type-safe post type repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
final class TypeSafePostTypeRepository implements PostTypeRepository {

	/**
	 * @var \WP_Post_Type[]
	 */
	private $available_post_types;

	/**
	 * Returns all post types that MultilingualPress is able to support.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Post_Type[] All post types that MultilingualPress is able to support.
	 */
	public function get_available_post_types(): array {

		if ( isset( $this->available_post_types ) ) {
			return $this->available_post_types;
		}

		$this->available_post_types = get_post_types( [
			'show_ui'  => true,
		], 'objects' );
		if ( $this->available_post_types ) {
			uasort( $this->available_post_types, function ( \WP_Post_Type $a, \WP_Post_Type $b ) {

				return strcasecmp( $a->labels->name, $b->labels->name );
			} );
		}

		return $this->available_post_types;
	}

	/**
	 * Returns the UI ID of the post type with the given slug.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Post type slug.
	 *
	 * @return string Post type UI ID.
	 */
	public function get_post_type_ui( string $slug ): string {

		$settings = $this->get_settings();

		return (string) (
			$settings[ $slug ][ PostTypeRepository::FIELD_UI ] ?? ''
		);
	}

	/**
	 * Checks if the post type with the given slug is active.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Post type slug.
	 *
	 * @return bool Whether or not the given post type is active.
	 */
	public function is_post_type_active( string $slug ): bool {

		$settings = $this->get_settings();

		return (bool) (
			$settings[ $slug ][ PostTypeRepository::FIELD_ACTIVE ] ?? false
		);
	}

	/**
	 * Checks if the post type with the given slug is set to be query-based.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Post type slug.
	 *
	 * @return bool Whether or not the given post type is set to be query-based.
	 */
	public function is_post_type_query_based( string $slug ): bool {

		$settings = $this->get_settings();

		return (bool) (
			$settings[ $slug ][ PostTypeRepository::FIELD_PERMALINK ] ?? false
		);
	}

	/**
	 * Sets post type support to the given post types.
	 *
	 * @since 3.0.0
	 *
	 * @param array $post_types Post type slugs.
	 *
	 * @return bool Whether the support for all given post types was set successfully.
	 */
	public function set_supported_post_types( array $post_types ): bool {

		return (bool) update_network_option( null, PostTypeRepository::OPTION, $post_types );
	}

	/**
	 * Removes the support for all post types.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the support for all post types was removed successfully.
	 */
	public function unset_supported_post_types(): bool {

		return $this->set_supported_post_types( [] );
	}

	/**
	 * Returns the post type support settings.
	 *
	 * @return array[] Post type support settings.
	 */
	private function get_settings(): array {

		return (array) get_network_option( null, PostTypeRepository::OPTION, [] );
	}
}
