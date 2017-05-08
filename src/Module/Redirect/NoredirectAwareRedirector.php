<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\HTTP\Request;

use function Inpsyde\MultilingualPress\call_exit;

/**
 * Interface for all redirector implementations.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class NoredirectAwareRedirector implements Redirector {

	/**
	 * @var LanguageNegotiator
	 */
	private $negotiator;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var NoredirectStorage
	 */
	private $storage;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param LanguageNegotiator $negotiator Language negotiator object.
	 * @param NoredirectStorage  $storage    Noredirect storage object.
	 * @param Request            $request    HTTP request object.
	 */
	public function __construct(
		LanguageNegotiator $negotiator,
		NoredirectStorage $storage,
		Request $request
	) {

		$this->negotiator = $negotiator;

		$this->storage = $storage;

		$this->request = $request;
	}

	/**
	 * Redirects the user to the best-matching language version, if any.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the user got redirected (for testing only).
	 */
	public function redirect(): bool {

		$value = $this->request->body_value( NoredirectStorage::KEY, INPUT_GET );

		if ( null !== $value ) {
			$this->storage->add_language( $value );

			return false;
		}

		$target = $this->negotiator->get_redirect_target();

		$current_site_id = get_current_blog_id();

		if ( $target->site_id() === $current_site_id ) {
			return false;
		}

		/**
		 * Filters the redirect URL.
		 *
		 * @since 3.0.0
		 *
		 * @param string         $url             Redirect URL.
		 * @param RedirectTarget $target          Redirect target object.
		 * @param int            $current_site_id Current site ID.
		 */
		$url = (string) apply_filters( Redirector::FILTER_URL, $target->url(), $target, $current_site_id );
		if ( ! $url ) {
			return false;
		}

		$this->storage->add_language( $target->language() );

		wp_redirect( $url );
		call_exit();

		return true;
	}
}
