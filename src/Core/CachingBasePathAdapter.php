<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\Common\BasePathAdapter;

/**
 * Base path adapter implementation using a non-persistent internal cache.
 *
 * Provides access to the correct basedir and baseurl paths of the current site's uploads folder.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
final class CachingBasePathAdapter implements BasePathAdapter {

	/**
	 * @var string[][]
	 */
	private $uploads_dirs = [];

	/**
	 * Returns the correct basedir path of the current site's uploads folder.
	 *
	 * @since 3.0.0
	 *
	 * @return string The correct basedir path of the current site's uploads folder.
	 */
	public function basedir(): string {

		$uploads = $this->get_uploads_dir();

		return (string) $uploads['basedir'];
	}

	/**
	 * Returns the correct baseurl path of the current site's uploads folder.
	 *
	 * @since 3.0.0
	 *
	 * @return string The correct baseurl path of the current site's uploads folder.
	 */
	public function baseurl(): string {

		$uploads = $this->get_uploads_dir();

		$base_url = (string) $uploads['baseurl'];

		if ( ! is_subdomain_install() ) {
			return $base_url;
		}

		$site_url = get_option( 'siteurl' );

		if ( 0 === strpos( $base_url, $site_url ) ) {
			return $base_url;
		}

		return str_replace(
			wp_parse_url( $base_url, PHP_URL_HOST ),
			wp_parse_url( $site_url, PHP_URL_HOST ),
			$base_url
		);
	}

	/**
	 * Returns the current site's uploads folder paths.
	 *
	 * @return string[] The current site's uploads folder paths.
	 */
	private function get_uploads_dir(): array {

		$current_site_id = get_current_blog_id();

		if ( empty( $this->uploads_dirs[ $current_site_id ] ) ) {
			$this->uploads_dirs[ $current_site_id ] = wp_upload_dir();
		}

		return $this->uploads_dirs[ $current_site_id ];
	}
}
