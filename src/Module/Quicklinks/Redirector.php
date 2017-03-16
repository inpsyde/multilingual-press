<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Quicklinks;

use Inpsyde\MultilingualPress\Common\Filter;

/**
 * Handles redirects issued by quicklinks.
 *
 * @package Inpsyde\MultilingualPress\Module\Quicklinks
 * @since   3.0.0
 */
class Redirector {

	/**
	 * @var Filter
	 */
	private $redirect_hosts_filter;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Filter $redirect_hosts_filter Redirect hosts filter object.
	 */
	public function __construct( Filter $redirect_hosts_filter ) {

		$this->redirect_hosts_filter = $redirect_hosts_filter;
	}

	/**
	 * Redirects to the given URL, if valid.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url The URL that is to be redirected to.
	 *
	 * @return bool Whether or not the redirection was successful.
	 */
	public function maybe_redirect( string $url ): bool {

		$this->redirect_hosts_filter->enable();

		$url = wp_validate_redirect( $url, false );

		$this->redirect_hosts_filter->disable();

		if ( ! $url ) {
			return false;
		}

		wp_redirect( $url, 303 );
		\Inpsyde\MultilingualPress\call_exit();

		return true;
	}
}
