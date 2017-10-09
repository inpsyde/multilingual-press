<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

/**
 * Site settings updater.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
class SiteSettingsUpdater {

	/**
	 * Action hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_DEFINE_INITIAL_SETTINGS = 'multilingualpress.define_initial_site_settings';

	/**
	 * Action hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_UPDATE_SETTINGS = 'multilingualpress.update_site_settings';

	/**
	 * @var Languages
	 */
	private $languages;

	/**
	 * @var SiteSettingsRepository
	 */
	private $repository;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingsRepository $repository Site settings repository object.
	 * @param Languages              $languages  Languages API object.
	 * @param Request                $request    HTTP request object.
	 */
	public function __construct( SiteSettingsRepository $repository, Languages $languages, Request $request ) {

		$this->repository = $repository;

		$this->languages = $languages;

		$this->request = $request;
	}

	/**
	 * Defines the initial settings of a new site.
	 *
	 * @since   3.0.0
	 * @wp-hook wpmu_new_blog
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	public function define_initial_settings( int $site_id ) {

		$this->update_wplang( $site_id );

		$this->update_language( $site_id );

		$this->update_alternative_language_title( $site_id );

		$this->update_relationships( $site_id );

		/**
		 * Fires right after the initial settings of a new site have been defined.
		 *
		 * @since 3.0.0
		 *
		 * @param int $site_id Site ID.
		 */
		do_action( self::ACTION_DEFINE_INITIAL_SETTINGS, (int) $site_id );
	}

	/**
	 * Updates the settings of an existing site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	public function update_settings( int $site_id ) {

		$this->update_language( $site_id );

		$this->update_alternative_language_title( $site_id );

		$this->update_relationships( $site_id );

		/**
		 * Fires right after the initial settings of an existing site have been updated.
		 *
		 * @since 3.0.0
		 *
		 * @param int $site_id Site ID.
		 */
		do_action( self::ACTION_UPDATE_SETTINGS, (int) $site_id );
	}

	/**
	 * Returns the language value from the request.
	 *
	 * @return string Language.
	 */
	private function get_language(): string {

		$language = $this->request->body_value(
			SiteSettingsRepository::NAME_LANGUAGE,
			INPUT_POST,
			FILTER_SANITIZE_STRING
		);
		if ( ! is_string( $language ) || '-1' === $language ) {
			$language = '';
		}

		return $language;
	}

	/**
	 * Updates the alternative language title for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function update_alternative_language_title( int $site_id ) {

		$alternative_language_title = $this->request->body_value(
			SiteSettingsRepository::NAME_ALTERNATIVE_LANGUAGE_TITLE,
			INPUT_POST,
			FILTER_SANITIZE_STRING
		);
		if ( ! is_string( $alternative_language_title ) ) {
			$alternative_language_title = '';
		}

		$this->repository->set_alternative_language_title( $alternative_language_title, $site_id );
	}

	/**
	 * Updates the language for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function update_language( int $site_id ) {

		$this->repository->set_language( $this->get_language(), $site_id );
	}

	/**
	 * Updates the relationships for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function update_relationships( int $site_id ) {

		$relationships = (array) $this->request->body_value(
			SiteSettingsRepository::NAME_RELATIONSHIPS,
			INPUT_POST,
			FILTER_SANITIZE_NUMBER_INT,
			FILTER_FORCE_ARRAY
		);

		$this->repository->set_relationships( array_map( 'intval', $relationships ), $site_id );
	}

	/**
	 * Updates the WordPress language for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function update_wplang( int $site_id ) {

		$language = $this->get_language();
		if ( ! $language ) {
			return;
		}

		$languages = $this->languages->get_languages( [
			'fields'     => LanguagesTable::COLUMN_LOCALE,
			'conditions' => [
				[
					'field' => LanguagesTable::COLUMN_HTTP_CODE,
					'value' => str_replace( '_', '-', $language ),
				],
			],
		] );

		$language = reset( $languages );
		if ( ! $language ) {
			return;
		}

		$wplang = $language[ LanguagesTable::COLUMN_LOCALE ];

		if ( in_array( $wplang, get_available_languages(), true ) ) {
			update_blog_option( $site_id, 'WPLANG', $wplang );
		}
	}
}
