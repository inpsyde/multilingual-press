<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core;

/**
 * Interface for all taxonomy repository implementations.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
interface TaxonomyRepository {

	/**
	 * Settings field name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FIELD_ACTIVE = 'active';

	/**
	 * Settings field name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FIELD_UI = 'ui';

	/**
	 * Option for storing the taxonomy setting.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const OPTION = 'mlp_taxonomy_settings';

	/**
	 * Returns all taxonomies that MultilingualPress is able to support.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Taxonomy[] All taxonomies that MultilingualPress is able to support.
	 */
	public function get_available_taxonomies(): array;

	/**
	 * Returns the UI ID of the taxonomy with the given slug.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Taxonomy slug.
	 *
	 * @return string Taxonomy UI ID.
	 */
	public function get_taxonomy_ui( string $slug ): string;

	/**
	 * Checks if the taxonomy with the given slug is active.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Taxonomy slug.
	 *
	 * @return bool Whether or not the given taxonomy is active.
	 */
	public function is_taxonomy_active( string $slug ): bool;

	/**
	 * Sets taxonomy support according to the given settings.
	 *
	 * @since 3.0.0
	 *
	 * @param array $taxonomies Taxonomy settings.
	 *
	 * @return bool Whether the support for all given taxonomies was set successfully.
	 */
	public function set_supported_taxonomies( array $taxonomies ): bool;

	/**
	 * Removes the support for all taxonomies.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the support for all taxonomies was removed successfully.
	 */
	public function unset_supported_taxonomies(): bool;
}
