<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\HTTP\Request;

use function Inpsyde\MultilingualPress\get_current_site_language;

/**
 * JavaScript-based redirector implementation.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class NoredirectAwareJavaScriptRedirector implements Redirector {

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

		$urls = $this->get_urls();
		if ( ! $urls ) {
			return false;
		}

		// There is currently no way to render a registered/enqueued script RIGHT NOW, so include it like so.
		?>
		<script>
			var mlpRedirectorSettings = {
				currentLanguage: <?php echo wp_json_encode( str_replace( '_', '-', get_current_site_language() ) ); ?>,
				noredirectKey: <?php echo wp_json_encode( NoredirectPermalinkFilter::QUERY_ARGUMENT ); ?>,
				urls: <?php echo wp_json_encode( $urls ); ?>,
			};
		</script>
		<script src="<?php echo WP_CONTENT_URL; ?>/plugins/multilingualpress/assets/js/redirect.js"></script>
		<?php

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
