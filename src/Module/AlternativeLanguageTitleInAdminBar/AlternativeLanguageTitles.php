<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\AlternativeLanguageTitleInAdminBar;

/**
 * Cached access to alternative language titles.
 *
 * @package Inpsyde\MultilingualPress\Module\AlternativeLanguageTitleInAdminBar
 * @since   3.0.0
 */
class AlternativeLanguageTitles {

	/**
	 * @var string
	 */
	private $cache_group = 'mlp';

	/**
	 * @var string
	 */
	private $cache_key = 'mlp_alternative_language_titles';

	/**
	 * Returns the alternative language title for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return string The alternative language title for the site with the given ID.
	 */
	public function get( $site_id = 0 ) {

		if ( ! $site_id ) {
			$site_id = get_current_blog_id();
		}

		$titles = wp_cache_get( $this->cache_key, $this->cache_group );
		if ( ! is_array( $titles ) ) {
			$titles = [];
		} elseif ( isset( $titles[ $site_id ] ) ) {
			return $titles[ $site_id ];
		}

		// TODO: Don't hardcode the option name.
		$languages = get_site_option( 'inpsyde_multilingual' );
		// TODO: Maybe also don't hardcode the 'text' key...?
		if ( ! isset( $languages[ $site_id ]['text'] ) ) {
			return '';
		}

		$title = $languages[ $site_id ]['text'];

		$titles[ $site_id ] = $title;

		wp_cache_set( $this->cache_key, $titles, $this->cache_group );

		return $title;
	}

	/**
	 * Updates the cache entry for the alternative language title of the updated site.
	 *
	 * @since   3.0.0
	 * @wp-hook mlp_blogs_save_fields
	 *
	 * @return bool Whether or not any alternative language titles were updated.
	 */
	public function update() {

		$site_id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : get_current_blog_id();
		if ( 1 > $site_id ) {
			return false;
		}

		$titles = wp_cache_get( $this->cache_key, $this->cache_group );
		if ( ! isset( $titles[ $site_id ] ) ) {
			return false;
		}

		unset( $titles[ $site_id ] );

		wp_cache_set( $this->cache_key, $titles, $this->cache_group );

		return true;
	}
}
