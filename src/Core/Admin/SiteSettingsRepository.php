<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\Admin;

/**
 * Interface for all site settings repository implementations.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
interface SiteSettingsRepository {

	/**
	 * Setting key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY_ALTERNATIVE_LANGUAGE_TITLE = 'text';

	/**
	 * Setting key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY_LANGUAGE = 'lang';

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_ALTERNATIVE_LANGUAGE_TITLE = 'mlp_alternative_language_title';

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_LANGUAGE = 'mlp_site_language';

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_RELATIONSHIPS = 'mlp_site_relations';

	/**
	 * Option name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const OPTION_SETTINGS = 'inpsyde_multilingual';

	/**
	 * Returns the alternative language title of the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string The alternative language title of the site with the given ID.
	 */
	public function get_alternative_language_title( int $site_id ): string;

	/**
	 * Returns the complete settings data.
	 *
	 * @since 3.0.0
	 *
	 * @return array The settings data.
	 */
	public function get_settings(): array;

	/**
	 * Returns an array with the IDs of all sites with an assigned language, minus the given IDs, if any.
	 *
	 * @since 3.0.0
	 *
	 * @param int[] $exclude Optional. Site IDs to exclude. Defaults to empty array.
	 *
	 * @return int[] An array with the IDs of all sites with an assigned language
	 */
	public function get_site_ids( array $exclude = [] ): array;

	/**
	 * Returns the site language of the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return string The site language of the site with the given ID, or the current site.
	 */
	public function get_site_language( int $site_id = 0 ): string;

	/**
	 * Sets the alternative language title for the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param string $title   Alternative language title.
	 * @param int    $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return bool Whether or not the alternative language title was set successfully.
	 */
	public function set_alternative_language_title( string $title, int $site_id = 0 ): bool;

	/**
	 * Sets the language for the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param string $language Language.
	 * @param int    $site_id  Optional. Site ID. Defaults to 0.
	 *
	 * @return bool Whether or not the language was set successfully.
	 */
	public function set_language( string $language, int $site_id = 0 ): bool;

	/**
	 * Sets the relationships for the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param int[] $site_ids     Site IDs.
	 * @param int   $base_site_id Optional. Base site ID. Defaults to 0.
	 *
	 * @return bool Whether or not the relationships were set successfully.
	 */
	public function set_relationships( array $site_ids, int $base_site_id = 0 ): bool;

	/**
	 * Sets the given settings data.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Settings data.
	 *
	 * @return bool Whether or not the settings data was set successfully.
	 */
	public function set_settings( array $settings ): bool;
}
