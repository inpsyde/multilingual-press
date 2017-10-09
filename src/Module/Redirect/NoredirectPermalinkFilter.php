<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\ContextAwareFilter;
use Inpsyde\MultilingualPress\Common\Filter;
use Inpsyde\MultilingualPress\Common\Type\Translation;

use function Inpsyde\MultilingualPress\get_available_languages;

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
	 * @var int
	 */
	private $accepted_args;

	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * @var string
	 */
	private $hook;

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
	public function add_noredirect_query_argument( $url, $site_id ): string {

		$url = (string) $url;
		if ( ! $url ) {
			return $url;
		}

		$languages = $this->languages();
		if ( empty( $languages[ $site_id ] ) ) {
			return $url;
		}

		$url = (string) add_query_arg( static::QUERY_ARGUMENT, $languages[ $site_id ], $url );

		return $url;
	}

	/**
	 * Removes the noredirect query argument from the given URL, if present.
	 *
	 * @since   3.0.0
	 * @wp-hook AlternateLanguages::FILTER_URL
	 *
	 * @param string $url URL.
	 *
	 * @return string The (filtered) URL.
	 */
	public function remove_noredirect_query_argument( string $url ): string {

		if ( $url && preg_match( '/(\?|&)' . self::QUERY_ARGUMENT . '=/', $url ) ) {
			$url = remove_query_arg( self::QUERY_ARGUMENT, $url );
		}

		return $url;
	}

	/**
	 * Returns the individual MultilingualPress language code of all (related) sites.
	 *
	 * @return string[] An array with site IDs as keys and the individual MultilingualPress language code as values.
	 */
	private function languages(): array {

		if ( ! isset( $this->languages ) ) {
			$this->languages = get_available_languages();
		}

		return $this->languages;
	}
}
