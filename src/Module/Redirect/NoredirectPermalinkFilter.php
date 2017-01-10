<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\ContextAwareFilter;
use Inpsyde\MultilingualPress\Common\Filter;
use Inpsyde\MultilingualPress\Common\Type\Translation;

/**
 * Permalink filter adding the noredirect query argument.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class NoredirectPermalinkFilter implements Filter {

	use ContextAwareFilter;

	/**
	 * Query argument.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const QUERY_ARGUMENT = 'noredirect';

	/**
	 * @var string[]
	 */
	private $languages;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->accepted_args = 2;

		$this->callback = [ $this, 'add_noredirect_query_argument' ];

		$this->hook = Translation::FILTER_URL;
	}

	/**
	 * Adds the noredirect query argument to the permalink, if applicable.
	 *
	 * @since   3.0.0
	 * @wp-hook Translation::FILTER_URL
	 *
	 * @param string $url     URL.
	 * @param int    $site_id Site ID.
	 *
	 * @return string The (filtered) URL.
	 */
	public function add_noredirect_query_argument( $url, $site_id ) {

		$url = (string) $url;
		if ( ! $url ) {
			return $url;
		}

		$languages = $this->languages();
		if ( empty( $languages[ $site_id ] ) ) {
			return $url;
		}

		$url = add_query_arg( static::QUERY_ARGUMENT, $languages[ $site_id ], $url );

		return $url;
	}

	/**
	 * Returns the individual MultilingualPress language code of all (related) sites.
	 *
	 * @return string[] An array with site IDs as keys and the individual MultilingualPress language code as values.
	 */
	private function languages() {

		if ( ! isset( $this->languages ) ) {
			$this->languages = \Inpsyde\MultilingualPress\get_available_languages();
		}

		return $this->languages;
	}
}
