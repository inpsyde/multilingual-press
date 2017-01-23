<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\Admin;

/**
 * Type-safe site settings repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class TypeSafeSiteSettingsRepository implements SiteSettingsRepository {

	/**
	 * @var string
	 */
	private $default_site_language = 'en_US';

	/**
	 * Returns the alternative language title of the site with the given ID, or the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return string The alternative language title of the site with the given ID, or the current site.
	 */
	public function get_alternative_language_title( $site_id = 0 ) {

		$site_id = (int) ( $site_id ?: get_current_blog_id() );

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
	public function get_flag_image_url( $site_id = 0 ) {

		$site_id = (int) ( $site_id ?: get_current_blog_id() );

		return (string) get_blog_option( $site_id, SiteSettingsRepository::OPTION_FLAG_IMAGE_URL, '' );
	}

	/**
	 * Returns the complete settings data.
	 *
	 * @since 3.0.0
	 *
	 * @return array The settings data.
	 */
	public function get_settings() {

		return (array) get_network_option( null, SiteSettingsRepository::OPTION_SETTINGS, [] );
	}

	/**
	 * Returns an array with the IDs of all sites with an assigned language, minus the given IDs, if any.
	 *
	 * @since 3.0.0
	 *
	 * @param int[]|int $exclude Optional. Site IDs to exclude. Defaults to empty array.
	 *
	 * @return int[] An array with the IDs of all sites with an assigned language
	 */
	public function get_site_ids( $exclude = [] ) {

		$settings = (array) get_network_option( null, SiteSettingsRepository::OPTION_SETTINGS, [] );
		if ( ! $settings ) {
			return [];
		}

		return array_unique( array_diff(
			array_map( 'intval', array_keys( $settings ) ),
			array_map( 'intval', (array) $exclude )
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
	public function get_site_language( $site_id = 0 ) {

		$site_id = (int) ( $site_id ?: get_current_blog_id() );

		$settings = get_network_option( null, SiteSettingsRepository::OPTION_SETTINGS, [] );

		if ( ! empty( $settings[ $site_id ]['lang'] ) ) {
			return (string) $settings[ $site_id ]['lang'];
		}

		$site_language = (string) get_network_option( null, 'WPLANG', $this->default_site_language );

		return in_array( $site_language, get_available_languages(), true )
			? $site_language
			: $this->default_site_language;
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
	public function set_settings( array $settings ) {

		return update_network_option( null, SiteSettingsRepository::OPTION_SETTINGS, $settings );
	}
}
