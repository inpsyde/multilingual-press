<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\AlternativeLanguageTitleInAdminBar;

/**
 * Replaces the site names in the admin bar with the respective alternative language titles.
 *
 * @package Inpsyde\MultilingualPress\Module\AlternativeLanguageTitleInAdminBar
 * @since   3.0.0
 */
class AdminBarCustomizer {

	/**
	 * @var AlternativeLanguageTitles
	 */
	private $titles;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param AlternativeLanguageTitles $titles Title cache object.
	 */
	public function __construct( AlternativeLanguageTitles $titles ) {

		$this->titles = $titles;
	}

	/**
	 * Replaces the current site's name with the site's alternative language title, if not empty.
	 *
	 * @since   3.0.0
	 * @wp-hook admin_bar_menu
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The WordPress admin bar object.
	 *
	 * @return \WP_Admin_Bar The manipulated WordPress admin bar object.
	 */
	public function replace_site_name( \WP_Admin_Bar $wp_admin_bar ): \WP_Admin_Bar {

		$title = $this->titles->get();
		if ( ! $title ) {
			return $wp_admin_bar;
		}

		$wp_admin_bar->add_node( [
			'id'    => 'site-name',
			'title' => $title,
		] );

		return $wp_admin_bar;
	}

	/**
	 * Replaces all site names with the individual site's alternative language title, if not empty.
	 *
	 * @since   3.0.0
	 * @wp-hook admin_bar_menu
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The WordPress admin bar object.
	 *
	 * @return \WP_Admin_Bar The manipulated WordPress admin bar object.
	 */
	public function replace_site_nodes( \WP_Admin_Bar $wp_admin_bar ): \WP_Admin_Bar {

		if ( empty( $wp_admin_bar->user->blogs ) ) {
			return $wp_admin_bar;
		}

		foreach ( (array) $wp_admin_bar->user->blogs as $site ) {
			if ( empty( $site->userblog_id ) ) {
				continue;
			}

			$title = $this->titles->get( (int) $site->userblog_id );
			if ( '' === $title ) {
				continue;
			}

			$wp_admin_bar->user->blogs[ $site->userblog_id ]->blogname = $title;
		}

		return $wp_admin_bar;
	}
}
