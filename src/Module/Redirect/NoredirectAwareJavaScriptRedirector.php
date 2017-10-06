<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Asset\AssetManager;

use function Inpsyde\MultilingualPress\get_current_site_language;

/**
 * JavaScript-based redirector implementation.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class NoredirectAwareJavaScriptRedirector implements Redirector {

	/**
	 * Hook name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_UPDATE_INTERVAL = 'multilingualpress.noredirect_update_interval';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var LanguageNegotiator
	 */
	private $negotiator;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param LanguageNegotiator $negotiator    Language negotiator object.
	 * @param AssetManager       $asset_manager Asset manager object.
	 */
	public function __construct( LanguageNegotiator $negotiator, AssetManager $asset_manager ) {

		$this->negotiator = $negotiator;

		$this->asset_manager = $asset_manager;
	}

	/**
	 * Redirects the user to the best-matching language version, if any.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the user got redirected (for testing only).
	 */
	public function redirect(): bool {

		$urls = $this->get_urls();
		if ( ! $urls ) {
			return false;
		}

		/**
		 * Filters the lifetime, in seconds, for data in the noredirect storage.
		 *
		 * @since 3.0.0
		 *
		 * @param int $lifetime The lifetime, in seconds, for data in the noredirect storage.
		 */
		$lifetime = (int) apply_filters( NoredirectStorage::FILTER_LIFETIME, NoredirectStorage::LIFETIME_IN_SECONDS );

		/**
		 * Filters the update interval, in seconds, for the timestamp of noredirect storage data.
		 *
		 * @since 3.0.0
		 *
		 * @param int $update_interval Update interval, in seconds, for the timestamp of noredirect storage data.
		 */
		$update_interval = (int) apply_filters( self::FILTER_UPDATE_INTERVAL, MINUTE_IN_SECONDS );

		$this->asset_manager->enqueue_script_with_data( 'multilingualpress-redirect', 'mlpRedirectorSettings', [
			'currentLanguage'         => str_replace( '_', '-', get_current_site_language() ),
			'noredirectKey'           => NoredirectPermalinkFilter::QUERY_ARGUMENT,
			'storageLifetime'         => absint( $lifetime * 1000 ),
			'updateTimestampInterval' => absint( $update_interval * 1000 ),
			'urls'                    => $urls,
		], false );

		return true;
	}

	/**
	 * Returns the URLs of all available language versions.
	 *
	 * @return string[] An array with language codes as keys and URLs as values.
	 */
	private function get_urls() {

		$targets = $this->negotiator->get_redirect_targets( [
			'strict' => false,
		] );
		if ( ! $targets ) {
			return [];
		}

		return array_reduce( $targets, function ( array $urls, RedirectTarget $target ) {

			$urls[ $target->language() ] = $target->url();

			return $urls;
		}, [] );
	}
}
