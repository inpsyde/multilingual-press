<?php # -*- coding: utf-8 -*-

/**
 * Replaces the site names in the admin bar with the according alternative language titles.
 */
class Mlp_Admin_Bar_Customizer {

	/**
	 * @var string
	 */
	private $cache_group = 'mlp';

	/**
	 * @var string
	 */
	private $cache_key = 'mlp_alternative_language_titles';

	/**
	 * Replaces all site names with the individual site's alternative language title, if not empty.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 *
	 * @return WP_Admin_Bar
	 */
	public function replace_site_nodes( WP_Admin_Bar $wp_admin_bar ) {

		if ( empty( $wp_admin_bar->user->blogs ) ) {
			return $wp_admin_bar;
		}

		foreach ( (array) $wp_admin_bar->user->blogs as $site ) {
			if ( empty( $site->userblog_id ) ) {
				continue;
			}

			$title = $this->get_title( $site->userblog_id );
			if ( ! $title ) {
				continue;
			}

			$wp_admin_bar->user->blogs[ $site->userblog_id ]->blogname = $title;
		}

		return $wp_admin_bar;
	}

	/**
	 * Replaces the current site's name with the site's alternative language title, if not empty.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 *
	 * @return WP_Admin_Bar
	 */
	public function replace_site_name( WP_Admin_Bar $wp_admin_bar ) {

		$title = $this->get_title();
		if ( ! $title ) {
			return $wp_admin_bar;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'site-name',
				'title' => $title,
			)
		);

		return $wp_admin_bar;
	}

	/**
	 * Updates the cache entry for the alternative language title of the updated site.
	 *
	 * @wp-hook mlp_blogs_save_fields
	 *
	 * @return void
	 */
	public function update_cache() {

		$site_id = absint( filter_input( INPUT_POST, 'id' ) );
		if ( ! $site_id ) {
			$site_id = get_current_blog_id();
		}
		if ( ! $site_id ) {
			return;
		}

		$titles = wp_cache_get( $this->cache_key, $this->cache_group );
		if ( ! isset( $titles[ $site_id ] ) ) {
			return;
		}

		unset( $titles[ $site_id ] );

		wp_cache_set( $this->cache_key, $titles, $this->cache_group );
	}

	/**
	 * Returns the alternative language title for the site with the given ID.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string
	 */
	private function get_title( $site_id = 0 ) {

		if ( ! $site_id ) {
			$site_id = get_current_blog_id();
		}

		$titles = wp_cache_get( $this->cache_key, $this->cache_group );
		if ( ! is_array( $titles ) ) {
			$titles = array();
		} elseif ( isset( $titles[ $site_id ] ) ) {
			return $titles[ $site_id ];
		}

		$settings = get_site_option( 'inpsyde_multilingual' );
		if ( ! isset( $settings[ $site_id ]['text'] ) ) {
			return '';
		}

		$title = $settings[ $site_id ]['text'];

		$titles[ $site_id ] = $title;
		wp_cache_set( $this->cache_key, $titles, $this->cache_group );

		return $title;
	}
}
