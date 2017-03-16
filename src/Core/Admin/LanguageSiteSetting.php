<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

/**
 * MultilingualPress "Language" site setting.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class LanguageSiteSetting implements SiteSettingViewModel {

	/**
	 * @var string
	 */
	private $id = 'mlp-site-language';

	/**
	 * @var Languages
	 */
	private $languages;

	/**
	 * @var SiteSettingsRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingsRepository $repository Site settings repository object.
	 * @param Languages              $languages  Languages API object.
	 */
	public function __construct( SiteSettingsRepository $repository, Languages $languages ) {

		$this->repository = $repository;

		$this->languages = $languages;
	}

	/**
	 * Returns the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string The markup for the site setting.
	 */
	public function markup( int $site_id ): string {

		return sprintf(
			'<select id="%2$s" name="blog[%3$s]" autocomplete="off">%1$s</select>',
			$this->get_options( $site_id ),
			esc_attr( $this->id ),
			esc_attr( SiteSettingsRepository::NAME_LANGUAGE )
		);
	}

	/**
	 * Returns the title of the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the site setting.
	 */
	public function title(): string {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Language', 'multilingual-press' ),
			esc_attr( $this->id )
		);
	}

	/**
	 * Returns the markup for all option tags.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string The markup for all option tags.
	 */
	private function get_options( int $site_id ): string {

		$options = '<option value="-1">' . esc_html__( 'Choose language', 'multilingual-press' ) . '</option>';

		$languages = $this->languages->get_all_languages();
		if ( $languages ) {
			$current_site_language = $this->repository->get_site_language( $site_id );

			$options = array_reduce( $languages, function ( $options, $language ) use ( $current_site_language ) {

				if (
					! empty( $language->http_name )
					&& ! ( empty( $language->english_name) && empty( $language->native_name ) )
				) {
					$site_language = str_replace( '-', '_', $language->http_name );

					$options .= sprintf(
						'<option value="%2$s"%3$s>%1$s</option>',
						esc_html( $this->get_language_name( $language ) ),
						esc_attr( $site_language ),
						selected( $site_language, $current_site_language, false )
					);
				}

				return $options;
			}, $options );
		}

		return $options;
	}

	/**
	 * Returns the name of the given language.
	 *
	 * @param object $language Language object.
	 *
	 * @return string The name of the given language.
	 */
	private function get_language_name( $language ): string {

		return implode( '/', array_filter( array_unique( [
			empty( $language->english_name ) ? '' : $language->english_name,
			empty( $language->native_name ) ? '' : $language->native_name,
		] ) ) );
	}
}
