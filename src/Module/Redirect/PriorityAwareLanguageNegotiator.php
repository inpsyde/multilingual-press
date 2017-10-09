<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\API\Translations;
use Inpsyde\MultilingualPress\Common\HTTP\HeaderParser;
use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Common\Type\Translation;

/**
 * Priority-aware language negotiator implementation.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class PriorityAwareLanguageNegotiator implements LanguageNegotiator {

	/**
	 * Filter hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_POST_STATUS = 'multilingualpress.redirect_post_status';

	/**
	 * Filter hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_PRIORITY_FACTOR = 'multilingualpress.language_only_priority_factor';

	/**
	 * Filter hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_REDIRECT_TARGETS = 'multilingualpress.redirect_targets';

	/**
	 * @var float
	 */
	private $language_only_priority_factor;

	/**
	 * @var HeaderParser
	 */
	private $parser;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Translations
	 */
	private $translations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Translations $translations Translations API object.
	 * @param Request      $request      HTTP request object.
	 * @param HeaderParser $parser       Accept-Language parser object.
	 */
	public function __construct( Translations $translations, Request $request, HeaderParser $parser ) {

		$this->translations = $translations;

		$this->request = $request;

		$this->parser = $parser;

		/**
		 * Filters the factor used to compute the priority of language-only matches. This has to be between 0 and 1.
		 *
		 * @see   get_user_priority()
		 * @since 2.4.8
		 *
		 * @param float $factor The factor used to compute the priority of language-only matches.
		 */
		$factor = (float) apply_filters( static::FILTER_PRIORITY_FACTOR, .8 );

		$this->language_only_priority_factor = (float) max( 0, min( 1, $factor ) );
	}

	/**
	 * Returns the redirect target data object for the best-matching language version.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Optional. Arguments required to determine the redirect targets. Defaults to empty array.
	 *
	 * @return RedirectTarget Redirect target object.
	 */
	public function get_redirect_target( array $args = [] ): RedirectTarget {

		/**
		 * Filters the allowed status for posts to be included as possible redirect targets.
		 *
		 * @since 2.8.0
		 *
		 * @param string[] $post_status Allowed post status.
		 */
		$post_status = (array) apply_filters( self::FILTER_POST_STATUS, [
			'publish',
		] );

		$translations = $this->translations->get_translations( array_merge( [
			'include_base' => true,
			'post_status'  => $post_status,
		], $args ) );
		if ( ! $translations ) {
			return new RedirectTarget();
		}

		$targets = $this->get_redirect_targets( $translations );
		if ( ! $targets ) {
			return new RedirectTarget();
		}

		uasort( $targets, function ( RedirectTarget $a, RedirectTarget $b ) {

			return $b->priority() <=> $a->priority();
		} );

		return reset( $targets );
	}

	/**
	 * Returns all possible redirect target objects for the given translations.
	 *
	 * @since 3.0.0
	 *
	 * @param Translation[] $translations Translation objects.
	 *
	 * @return RedirectTarget[] An array of redirect target objects.
	 */
	private function get_redirect_targets( array $translations ): array {

		$user_languages = $this->get_user_languages();
		if ( ! $user_languages ) {
			return [];
		}

		$translations = array_filter( $translations, function ( Translation $translation ) {

			return $translation->remote_url();
		} );

		$targets = [];

		array_walk( $translations, function ( Translation $translation, $site_id, $user_languages ) use ( &$targets ) {

			$language = $translation->language();

			$user_priority = $this->get_language_priority( $language, $user_languages );

			if ( 0 < $user_priority ) {
				$targets[] = new RedirectTarget( [
					RedirectTarget::KEY_CONTENT_ID => $translation->target_content_id(),
					RedirectTarget::KEY_LANGUAGE   => $language->name( 'http' ),
					RedirectTarget::KEY_PRIORITY   => $language->priority() * $user_priority,
					RedirectTarget::KEY_SITE_ID    => $site_id,
					RedirectTarget::KEY_URL        => $translation->remote_url(),
				] );
			}
		}, $user_languages );

		/**
		 * Filters the possible redirect target objects.
		 *
		 * @since 3.0.0
		 *
		 * @param RedirectTarget[] $targets      Possible redirect target objects.
		 * @param Translation[]    $translations Translation objects.
		 */
		$targets = (array) apply_filters( self::FILTER_REDIRECT_TARGETS, $targets, $translations );

		return array_filter( $targets, function ( $target ) {

			return $target instanceof RedirectTarget;
		} );
	}

	/**
	 * Returns the user languages included in the Accept-Language header.
	 *
	 * @return float[] An array with language codes as keys, and priorities as values.
	 */
	private function get_user_languages(): array {

		$fields = $this->request->parsed_header( 'ACCEPT_LANGUAGE', $this->parser );
		if ( ! $fields ) {
			return [];
		}

		$user_languages = [];

		array_walk( $fields, function ( $priority, $code ) use ( &$user_languages ) {

			$user_languages[ strtolower( $code ) ] = $priority;

			if ( strpos( $code, '-' ) ) {
				$code = strtolower( strtok( $code, '-' ) );
				if ( ! isset( $user_languages[ $code ] ) ) {
					$user_languages[ $code ] = $priority;
				}
			}
		} );

		return $user_languages;
	}

	/**
	 * Returns the priority of the given language.
	 *
	 * @param Language $language  Language object.
	 * @param float[]  $languages Language priorities.
	 *
	 * @return float User priority.
	 */
	private function get_language_priority( Language $language, array $languages ): float {

		$lang_http = strtolower( $language->name( 'http_code' ) );

		if ( isset( $languages[ $lang_http ] ) ) {
			return (float) $languages[ $lang_http ];
		}

		$lang_short = strtolower( $language->name( 'language_short' ) );

		if ( isset( $languages[ $lang_short ] ) ) {
			return (float) $this->language_only_priority_factor * $languages[ $lang_short ];
		}

		return 0.0;
	}
}
