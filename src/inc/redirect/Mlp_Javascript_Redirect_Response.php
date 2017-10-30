<?php # -*- coding: utf-8 -*-

/**
 * Sends redirect headers.
 */
class Mlp_Javascript_Redirect_Response implements Mlp_Redirect_Response_Interface {

	/**
	 * @var Mlp_Locations_Interface
	 */
	private $locations;

	/**
	 * @var Mlp_Language_Negotiation_Interface
	 */
	private $negotiation;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Language_Negotiation_Interface $negotiation Language negotiation object.
	 * @param Mlp_Locations_Interface            $locations
	 */
	public function __construct( Mlp_Language_Negotiation_Interface $negotiation, Mlp_Locations_Interface $locations ) {

		$this->negotiation = $negotiation;

		$this->locations = $locations;
	}

	/**
	 * Redirects if needed.
	 *
	 * @return bool
	 */
	public function redirect() {

		$urls = $this->get_urls();
		if ( ! $urls ) {
			return false;
		}

		$handle = 'multilingualpress-redirect';

		$url = new Mlp_Asset_Url(
			'redirect.js',
			$this->locations->get_dir( 'js', 'path' ),
			$this->locations->get_dir( 'js', 'url' )
		);

		wp_enqueue_script(
			$handle,
			(string) $url,
			array(),
			$url->get_version()
		);

		/**
		 * Filters the lifetime, in seconds, for data in the noredirect storage.
		 *
		 * @since 2.10.0
		 *
		 * @param int $lifetime The lifetime, in seconds, for data in the noredirect storage.
		 */
		$lifetime = (int) apply_filters( 'multilingualpress.noredirect_storage_lifetime', 5 * MINUTE_IN_SECONDS );

		/**
		 * Filters the update interval, in seconds, for the timestamp of noredirect storage data.
		 *
		 * @since 2.10.0
		 *
		 * @param int $update_interval Update interval, in seconds, for the timestamp of noredirect storage data.
		 */
		$update_interval = apply_filters( 'multilingualpress.noredirect_update_interval', MINUTE_IN_SECONDS );

		wp_localize_script(
			$handle,
			'mlpRedirectorSettings',
			array(
				'currentLanguage'         => str_replace( '_', '-', mlp_get_current_blog_language() ),
				'noredirectKey'           => 'noredirect',
				'storageLifetime'         => absint( $lifetime * 1000 ),
				'updateTimestampInterval' => absint( $update_interval * 1000 ),
				'urls'                    => $urls,
			)
		);

		return true;
	}

	/**
	 * Registers the redirection using the appropriate hook.
	 *
	 * @return void
	 */
	public function register() {

		$this->redirect();
	}

	/**
	 * Returns the URLs of all available language versions.
	 *
	 * @return string[] An array with language codes as keys and URLs as values.
	 */
	private function get_urls() {

		$targets = $this->negotiation->get_redirect_targets( array(
			'strict' => false,
		) );
		if ( ! $targets ) {
			return array();
		}

		$urls = array();

		foreach ( $targets as $target ) {
			$urls[ $target['language'] ] = $target['url'];
		}

		return $urls;
	}
}
