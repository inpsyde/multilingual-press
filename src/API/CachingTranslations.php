<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Common\Type\Translation;
use Inpsyde\MultilingualPress\Translation\Translator;
use Inpsyde\MultilingualPress\Translation\Translator\NullTranslator;
use Inpsyde\MultilingualPress\Common\Request;
use Inpsyde\MultilingualPress\Factory\TypeFactory;

use function Inpsyde\MultilingualPress\get_flag_url_for_site;

/**
 * Caching translations API implementation.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
final class CachingTranslations implements Translations {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var Languages
	 */
	private $languages;

	/**
	 * @var NullTranslator
	 */
	private $null_translator;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var Translator[]
	 */
	private $translators = [];

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * @var string[]
	 */
	private $unfiltered_translations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations    $site_relations    Site relations API object.
	 * @param ContentRelations $content_relations Content relations API object.
	 * @param Languages        $languages         Languages API object.
	 * @param Request          $request           Request object.
	 * @param TypeFactory      $type_factory      Type factory object.
	 */
	public function __construct(
		SiteRelations $site_relations,
		ContentRelations $content_relations,
		Languages $languages,
		Request $request,
		TypeFactory $type_factory
	) {

		$this->site_relations = $site_relations;

		$this->content_relations = $content_relations;

		$this->languages = $languages;

		$this->request = $request;

		$this->type_factory = $type_factory;
	}

	/**
	 * Returns all translations according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Optional. Arguments required to fetch the translations. Defaults to empty array.
	 *
	 * @return Translation[] An array with site IDs as keys and Translation objects as values.
	 */
	public function get_translations( array $args = [] ): array {

		$args = $this->normalize_arguments( $args );

		$key = md5( serialize( $args ) );

		$translations = wp_cache_get( $key, 'mlp' );
		if ( is_array( $translations ) ) {
			return $translations;
		}

		$translations = $this->build_translations( $args );

		/**
		 * Filter the translations before they are used.
		 *
		 * @param Translation[] $translations Translations.
		 * @param array         $args         Translation args.
		 */
		$translations = (array) apply_filters( 'mlp_translations', $translations, $args );

		wp_cache_set( $key, $translations, 'mlp' );

		return $translations;
	}

	/**
	 * Actually builds and then returns all translations according to the given arguments.
	 *
	 * @param array $args
	 *
	 * @return Translation[] An array with site IDs as keys and Translation objects as values.
	 */
	private function build_translations( array $args = [] ): array {

		$source_site_id = (int) $args['site_id'];

		$site_ids = $this->site_relations->get_related_site_ids( $source_site_id, (bool) $args['include_base'] );
		if ( ! $site_ids ) {
			return [];
		}

		$type = (string) $args['type'];

		$content_relations = 0 < $args['content_id']
			? $this->content_relations->get_relations( $source_site_id, (int) $args['content_id'], $type )
			: [];

		if ( ! $content_relations && $args['strict'] ) {
			return [];
		}

		$languages = $this->languages->get_all_site_languages();

		$site_ids = array_intersect( $site_ids, array_keys( $languages ) );
		if ( ! $site_ids ) {
			return [];
		}

		$default_translation = [
			'remote_title'      => '',
			'remote_url'        => '',
			'source_site_id'    => $source_site_id,
			'suppress_filters'  => (bool) $args['suppress_filters'],
			'target_content_id' => 0,
			'type'              => $type,
		];

		$translations = [];

		foreach ( $site_ids as $site_id ) {
			$site_id = (int) $site_id;

			$translation = [];

			if ( empty( $content_relations[ $site_id ] ) ) {
				$translation = $this->get_translation_for_no_related_content( $site_id, $args );
				if ( ! $translation['remote_url'] ) {
					continue;
				}
			} else {
				if ( in_array( $type, [ Request::TYPE_SINGULAR, Request::TYPE_TERM_ARCHIVE ], true ) ) {
					$content_id = (int) $content_relations[ $site_id ];

					$translation = $this->get_translation_for_related_content( $site_id, $content_id, $args );
					if ( ! $translation ) {
						continue;
					}

					$translation = array_merge( [ 'target_content_id' => $content_id ], $translation );
				}
			}

			$translation = array_merge( $default_translation, [ 'target_site_id' => $site_id ], $translation );

			$language = $languages[ $site_id ];
			if ( empty( $language['http_name'] ) ) {
				$language['http_name'] = empty( $language['lang'] ) ? '' : $language['lang'];
			}

			$translation['icon_url'] = $language['http_name'] ? get_flag_url_for_site( $site_id ) : '';

			$translations[ $site_id ] = $this->type_factory->create_translation( [
				$translation,
				$this->type_factory->create_language( [
					$language,
				] ),
			] );
		}

		return $translations;
	}

	/**
	 * Returns the translation data for a request that IS for a related content element.
	 *
	 * @param int   $site_id    Site ID.
	 * @param int   $content_id Content ID.
	 * @param array $args       Arguments required to fetch the translations.
	 *
	 * @return array Translation data.
	 */
	private function get_translation_for_related_content( int $site_id, int $content_id, array $args ): array {

		$type = (string) $args['type'];

		$translator = $this->translator( $type );

		$translation = [];

		switch ( $type ) {
			case Request::TYPE_SINGULAR:
				$translation = $translator->get_translation( $site_id, [
					'content_id' => $content_id,
					'strict'     => (bool) $args['strict'],
				] );
				break;

			case Request::TYPE_TERM_ARCHIVE:
				$translation = $translator->get_translation( $site_id, [
					'content_id' => $content_id,
				] );
				break;
		}

		return $translation;
	}

	/**
	 * Returns the translation data for a request that is NOT for a related content element.
	 *
	 * @param int   $site_id Site ID.
	 * @param array $args    Arguments required to fetch the translations.
	 *
	 * @return array Translation data.
	 */
	private function get_translation_for_no_related_content( int $site_id, array $args ): array {

		$type = (string) $args['type'];

		$translator = $this->translator( $type );

		$translation = [];

		switch ( $type ) {
			case Request::TYPE_POST_TYPE_ARCHIVE:
				$translation = $translator->get_translation( $site_id, [
					'post_type' => (string) $args['post_type'],
				] );
				break;

			case Request::TYPE_SEARCH:
				$translation = $translator->get_translation( $site_id, [
					'query' => (string) $args['search_term'],
				] );
				break;
		}

		if (
			Request::TYPE_FRONT_PAGE === $type
			|| ( empty( $translation['remote_url'] ) && ! $args['strict'] )
		) {
			$translation = array_merge(
				$translation,
				$this->translator( Request::TYPE_FRONT_PAGE )->get_translation( $site_id )
			);
		}

		return $translation;
	}

	/**
	 * Returns the unfiltered translations.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] Array with HTTP language codes as keys and URLs as values.
	 */
	public function get_unfiltered_translations(): array {

		if ( isset( $this->unfiltered_translations ) ) {
			return $this->unfiltered_translations;
		}

		$this->unfiltered_translations = [];

		$translations = $this->get_translations( [
			'include_base'     => true,
			'suppress_filters' => true,
		] );
		if ( ! $translations ) {
			return $this->unfiltered_translations;
		}

		array_walk( $translations, function ( Translation $translation ) {

			$url = $translation->remote_url();
			if ( $url ) {
				$this->unfiltered_translations[ $translation->language()->name( 'http' ) ] = $url;
			}
		} );

		return $this->unfiltered_translations;
	}

	/**
	 * Registers the given translator for the given type.
	 *
	 * @since 3.0.0
	 *
	 * @param Translator $translator Translator object.
	 * @param string     $type       Request or content type.
	 *
	 * @return bool Whether or not the translator was registered successfully.
	 */
	public function register_translator( Translator $translator, string $type ): bool {

		if ( isset( $this->translators[ $type ] ) ) {
			return false;
		}

		$this->translators[ $type ] = $translator;

		return true;
	}

	/**
	 * Returns a normalized arguments array according to the one passed, but with all missing defaults.
	 *
	 * @param array $args Arguments required to fetch the translations.
	 *
	 * @return array Arguments required to fetch the translations.
	 */
	private function normalize_arguments( array $args ): array {

		$args = array_merge( [
			'content_id'       => $this->request->queried_object_id(),
			'include_base'     => false,
			'post_type'        => $this->request->post_type(),
			'search_term'      => get_search_query(),
			'site_id'          => get_current_blog_id(),
			'strict'           => true,
			'suppress_filters' => false,
			'type'             => $this->request->type(),
		], $args );

		/**
		 * Filters the arguments required to fetch the translations.
		 *
		 * @since 3.0.0
		 *
		 * @param array $args Arguments required to fetch the translations.
		 */
		$args = (array) apply_filters( 'mlp_get_translations_args', $args );

		return $args;
	}

	/**
	 * Returns the null translator instance.
	 *
	 * @return NullTranslator Translator object.
	 */
	private function null_translator(): NullTranslator {

		if ( ! $this->null_translator ) {
			$this->null_translator = new NullTranslator();
		}

		return $this->null_translator;
	}

	/**
	 * Returns the translator instance for the given type.
	 *
	 * @param string $type Request or content type.
	 *
	 * @return Translator Translator object.
	 */
	private function translator( string $type ): Translator {

		return $this->translators[ $type ] ?? $this->null_translator();
	}
}
