<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\API\SiteRelations;

/**
 * Type-safe site settings repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class TypeSafeSiteSettingsRepository implements SiteSettingsRepository {

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations $site_relations Site relations API object.
	 */
	public function __construct( SiteRelations $site_relations ) {

		$this->site_relations = $site_relations;
	}

	/**
	 * Returns the alternative language title of the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return string The alternative language title of the site with the given ID, or the current site.
	 */
	public function get_alternative_language_title( int $site_id = 0 ): string {

		$site_id = $site_id ?: get_current_blog_id();

		$settings = get_network_option( null, SiteSettingsRepository::OPTION_SETTINGS, [] );

		return empty( $settings[ $site_id ]['text'] ) ? '' : stripslashes( $settings[ $site_id ]['text'] );
	}

	/**
	 * Returns the flag image URL of the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return string The flag image URL of the site with the given ID, or the current site.
	 */
	public function get_flag_image_url( int $site_id = 0 ): string {

		$site_id = $site_id ?: get_current_blog_id();

		return (string) get_blog_option( $site_id, SiteSettingsRepository::OPTION_FLAG_IMAGE_URL, '' );
	}

	/**
	 * Returns the complete settings data.
	 *
	 * @since 3.0.0
	 *
	 * @return array The settings data.
	 */
	public function get_settings(): array {

		return (array) get_network_option( null, SiteSettingsRepository::OPTION_SETTINGS, [] );
	}

	/**
	 * Returns an array with the IDs of all sites with an assigned language, minus the given IDs, if any.
	 *
	 * @since 3.0.0
	 *
	 * @param int[] $exclude Optional. Site IDs to exclude. Defaults to empty array.
	 *
	 * @return int[] An array with the IDs of all sites with an assigned language
	 */
	public function get_site_ids( array $exclude = [] ): array {

		$settings = (array) get_network_option( null, SiteSettingsRepository::OPTION_SETTINGS, [] );
		if ( ! $settings ) {
			return [];
		}

		return array_unique( array_diff(
			array_map( 'intval', array_keys( $settings ) ),
			array_map( 'intval', $exclude )
		) );
	}

	/**
	 * Returns the site language of the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return string The site language of the site with the given ID, or the current site.
	 */
	public function get_site_language( int $site_id = 0 ): string {

		$site_id = $site_id ?: get_current_blog_id();

		$settings = get_network_option( null, SiteSettingsRepository::OPTION_SETTINGS, [] );

		if ( ! empty( $settings[ $site_id ]['lang'] ) ) {
			return (string) $settings[ $site_id ]['lang'];
		}

		$site_language = (string) get_network_option( null, 'WPLANG', '' );

		return in_array( $site_language, get_available_languages(), true )
			? $site_language
			: '';
	}

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
	public function set_alternative_language_title( string $title, int $site_id = 0 ): bool {

		return $this->update_setting( SiteSettingsRepository::KEY_ALTERNATIVE_LANGUAGE_TITLE, $title, $site_id );
	}

	/**
	 * Sets the flag image URL for the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url     Flag image URL.
	 * @param int    $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return bool Whether or not the flag image URL was set successfully.
	 */
	public function set_flag_image_url( string $url, int $site_id = 0 ): bool {

		$site_id = $site_id ?: get_current_blog_id();

		return (bool) update_blog_option( $site_id, SiteSettingsRepository::OPTION_FLAG_IMAGE_URL, esc_url( $url ) );
	}

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
	public function set_language( string $language, int $site_id = 0 ): bool {

		return $this->update_setting( SiteSettingsRepository::KEY_LANGUAGE, $language, $site_id );
	}

	/**
	 * Sets the relationships for the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param int[] $site_ids     Site IDs.
	 * @param int   $base_site_id Optional. Base site ID. Defaults to 0.
	 *
	 * @return bool Whether or not the language was set successfully.
	 */
	public function set_relationships( array $site_ids, int $base_site_id = 0 ): bool {

		$base_site_id = $base_site_id ?: get_current_blog_id();

		return (bool) $this->site_relations->set_relationships( $base_site_id, $site_ids );
	}

	/**
	 * Sets the given settings data.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Settings data.
	 *
	 * @return bool Whether or not the settings data was set successfully.
	 */
	public function set_settings( array $settings ): bool {

		return (bool) update_network_option( null, SiteSettingsRepository::OPTION_SETTINGS, $settings );
	}

	/**
	 * Updates the given setting for the site with the given ID, or the current site.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $value   Setting value.
	 * @param int    $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return bool Whether or not the setting was updated successfully.
	 */
	private function update_setting( string $key, $value, int $site_id = 0 ): bool {

		$site_id = $site_id ?: get_current_blog_id();

		$settings = $this->get_settings();

		if ( ! isset( $settings[ $site_id ] ) || ! is_array( $settings[ $site_id ] ) ) {
			$settings[ $site_id ] = [];
		}

		$settings[ $site_id ][ $key ] = $value;

		return $this->set_settings( $settings );
	}
}
