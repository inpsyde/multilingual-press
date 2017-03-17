<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Setting\Site;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

/**
 * Site setting updater implementation validating a nonce specific to the update action included in the request data.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\Site
 * @since   3.0.0
 */
final class SecureSiteSettingUpdater implements SiteSettingUpdater {

	/**
	 * @var string
	 */
	private $option;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option Site option name.
	 * @param Nonce  $nonce  Optional. Nonce object. Defaults to null.
	 */
	public function __construct( string $option, Nonce $nonce = null ) {

		$this->option = $option;

		$this->nonce = $nonce;
	}

	/**
	 * Updates the setting with the given data for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the site setting was updated successfully.
	 */
	public function update( int $site_id ): bool {

		if ( ! current_user_can( 'manage_sites' ) ) {
			return false;
		}

		if ( $this->nonce && ! $this->nonce->is_valid() ) {
			return false;
		}

		$value = $this->get_value();

		return $value
			? update_blog_option( $site_id, $this->option, $value )
			: delete_blog_option( $site_id, $this->option );
	}

	/**
	 * Returns the value included in the request.
	 *
	 * @return string The value included in the request.
	 */
	private function get_value(): string {

		$value = is_string( $_GET[ $this->option ] ?? null ) ? $_GET[ $this->option ] : '';

		$request_method = $_SERVER['REQUEST_METHOD'] ?? '';
		if ( ! $request_method || 'POST' !== strtoupper( $request_method ) ) {
			return $value;
		}

		return is_string( $_POST[ $this->option ] ?? null ) ? $_POST[ $this->option ] : '';
	}
}
