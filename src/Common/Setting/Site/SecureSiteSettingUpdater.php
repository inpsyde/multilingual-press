<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Setting\Site;

use Inpsyde\MultilingualPress\Common\Http\Request;
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
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string  $option  Site option name.
	 * @param Request $request HTTP request abstraction
	 * @param Nonce   $nonce   Optional. Nonce object. Defaults to null.
	 */
	public function __construct( string $option, Request $request, Nonce $nonce = null ) {

		$this->option = $option;

		$this->nonce = $nonce;

		$this->request = $request;
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

		$value = $this->request->body_value( $this->option );

		return $value
			? update_blog_option( $site_id, $this->option, $value )
			: delete_blog_option( $site_id, $this->option );
	}
}
