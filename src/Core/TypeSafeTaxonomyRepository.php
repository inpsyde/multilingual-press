<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core;

/**
 * Type-safe taxonomy repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
final class TypeSafeTaxonomyRepository implements TaxonomyRepository {

	/**
	 * @var \WP_Taxonomy[]
	 */
	private $available_taxonomies;

	/**
	 * Returns all taxonomies that MultilingualPress is able to support.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Taxonomy[] All taxonomies that MultilingualPress is able to support.
	 */
	public function get_available_taxonomies(): array {

		if ( isset( $this->available_taxonomies ) ) {
			return $this->available_taxonomies;
		}

		$this->available_taxonomies = get_taxonomies( [
			'show_ui' => true,
		], 'objects' );
		if ( $this->available_taxonomies ) {
			uasort( $this->available_taxonomies, function ( \WP_Taxonomy $a, \WP_Taxonomy $b ) {

				return strcasecmp( $a->labels->name, $b->labels->name );
			} );
		}

		return $this->available_taxonomies;
	}

	/**
	 * Returns the UI ID of the taxonomy with the given slug.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Taxonomy slug.
	 *
	 * @return string Taxonomy UI ID.
	 */
	public function get_taxonomy_ui( string $slug ): string {

		$settings = $this->get_settings();

		return (string) (
			$settings[ $slug ][ TaxonomyRepository::FIELD_UI ] ?? ''
		);
	}

	/**
	 * Returns all taxonomies supported by MultilingualPress.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] Taxonomy slugs.
	 */
	public function get_supported_taxonomies() {

		$settings = $this->get_settings();

		return array_filter( array_keys( $settings ), function ( string $slug ) {

			return $this->is_taxonomy_active( $slug );
		} );
	}

	/**
	 * Checks if the taxonomy with the given slug is active.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Taxonomy slug.
	 *
	 * @return bool Whether or not the given taxonomy is active.
	 */
	public function is_taxonomy_active( string $slug ): bool {

		$settings = $this->get_settings();

		return (bool) (
			$settings[ $slug ][ TaxonomyRepository::FIELD_ACTIVE ] ?? false
		);
	}

	/**
	 * Sets taxonomy support according to the given settings.
	 *
	 * @since 3.0.0
	 *
	 * @param array $taxonomies Taxonomy settings.
	 *
	 * @return bool Whether the support for all given taxonomies was set successfully.
	 */
	public function set_supported_taxonomies( array $taxonomies ): bool {

		return (bool) update_network_option( null, TaxonomyRepository::OPTION, $taxonomies );
	}

	/**
	 * Removes the support for all taxonomies.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the support for all taxonomies was removed successfully.
	 */
	public function unset_supported_taxonomies(): bool {

		return $this->set_supported_taxonomies( [] );
	}

	/**
	 * Returns the taxonomy support settings.
	 *
	 * @return array[] Taxonomy support settings.
	 */
	private function get_settings(): array {

		return (array) get_network_option( null, TaxonomyRepository::OPTION, [] );
	}
}
