<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\API\Translations;
use Inpsyde\MultilingualPress\Common\AcceptHeader\AcceptHeaderParser;
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
	const FILTER_PRIORITY_FACTOR = 'multilingualpress.language_only_priority_factor';

	/**
	 * @var float
	 */
	private $language_only_priority_factor;

	/**
	 * @var AcceptHeaderParser
	 */
	private $parser;

	/**
	 * @var Translations
	 */
	private $translations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Translations       $translations Translations API object.
	 * @param AcceptHeaderParser $parser       Accept-Language parser object.
	 */
	public function __construct( Translations $translations, AcceptHeaderParser $parser ) {

		$this->translations = $translations;

		$this->parser = $parser;

		/**
		 * Filters the factor used to compute the priority of language-only matches. This has to be between 0 and 1.
		 *
		 * @see   get_user_priority()
		 * @since 2.4.8
		 *
		 * @param float $factor The factor used to compute the priority of language-only matches.
		 */
		$factor = (float) apply_filters( self::FILTER_PRIORITY_FACTOR, .7 );

		$this->language_only_priority_factor = max( 0, min( 1, $factor ) );
	}

	/**
	 * Returns the redirect target data object for the best-matching language version.
	 *
	 * @since 3.0.0
	 *
	 * @return RedirectTarget Redirect target object.
	 */
	public function get_redirect_target() {

		$translations = $this->translations->get_translations( [
			'include_base' => true,
		] );
		if ( ! $translations ) {
			return new RedirectTarget();
		}

		$targets = $this->get_redirect_targets( $translations );
		if ( ! $targets ) {
			return new RedirectTarget();
		}

		uasort( $targets, function ( RedirectTarget $a, RedirectTarget $b ) {

			return $a->priority() - $b->priority();
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
	private function get_redirect_targets( array $translations ) {

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
					RedirectTarget::KEY_PRIORITY => $language->priority() * $user_priority,
					RedirectTarget::KEY_URL      => $translation->remote_url(),
					RedirectTarget::KEY_LANGUAGE => $language->name( 'http' ),
					RedirectTarget::KEY_SITE_ID  => $site_id,
				] );
			}
		}, $user_languages );

		return $targets;
	}

	/**
	 * Returns the user languages included in the Accept-Language header.
	 *
	 * @return float[] An array with language codes as keys, and priorities as values.
	 */
	private function get_user_languages() {

		$fields = $this->parser->parse( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		if ( ! $fields ) {
			return [];
		}

		$user_languages = [];

		array_walk( $fields, function ( $priority, $code ) use ( &$user_languages ) {

			$out[ strtolower( $code ) ] = $priority;

			if ( strpos( $code, '-' ) ) {
				$code = strtolower( strtok( $code, '-' ) );
				if ( ! isset( $out[ $code ] ) ) {
					$out[ $code ] = $priority;
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
	private function get_language_priority( Language $language, array $languages ) {

		$lang_http = strtolower( $language->name( 'http_name' ) );

		if ( isset( $languages[ $lang_http ] ) ) {
			return $languages[ $lang_http ];
		}

		$lang_short = strtolower( $language->name( 'language_short' ) );

		if ( isset( $languages[ $lang_short ] ) ) {
			return $this->language_only_priority_factor * $languages[ $lang_short ];
		}

		return 0;
	}
}
