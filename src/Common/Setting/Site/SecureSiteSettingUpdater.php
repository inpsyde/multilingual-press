<?php # -*- coding: utf-8 -*-

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
	public function __construct( $option, Nonce $nonce = null ) {

		$this->option = (string) $option;

		$this->nonce = $nonce;
	}

	/**
	 * Updates the setting with the given data for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data    Data to be saved.
	 * @param int   $site_id Site ID.
	 *
	 * @return bool Whether or not the site setting was updated successfully.
	 */
	public function update( array $data, $site_id ) {

		if ( ! current_user_can( 'manage_sites' ) ) {
			return false;
		}

		if ( $this->nonce && ! $this->nonce->is_valid() ) {
			return false;
		}

		$value = $this->get_value( $data );

		return $value
			? update_blog_option( $site_id, $this->option, $value )
			: delete_blog_option( $site_id, $this->option );
	}

	/**
	 * Returns the value included in the data array.
	 *
	 * @param array $data Data to be saved.
	 *
	 * @return string The value included in the data array.
	 */
	private function get_value( array $data ) {

		return array_key_exists( $this->option, $data ) && is_string( $data[ $this->option ] )
			? $data[ $this->option ]
			: '';
	}
}
