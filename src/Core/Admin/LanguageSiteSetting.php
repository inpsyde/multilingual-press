<?php # -*- coding: utf-8 -*-

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
	private $default_language;

	/**
	 * @var string
	 */
	private $id = 'mlp-site-language';

	/**
	 * @var Languages
	 */
	private $languages;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Languages $languages Languages API object.
	 */
	public function __construct( Languages $languages ) {

		$this->languages = $languages;

		$default_language = get_site_option( 'WPLANG' );

		$this->default_language = in_array( $default_language, get_available_languages(), true )
			? str_replace( '_', '-', $default_language )
			: 'en-US';
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
	public function markup( $site_id ) {

		// TODO: Adapt to be used on Edit Site as well.
		return sprintf(
			'<select id="%2$s" name="blog[%3$s]" autocomplete="off">%1$s</select>',
			$this->get_options(),
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
	public function title() {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Language', 'multilingual-press' ),
			esc_attr( $this->id )
		);
	}

	/**
	 * Returns the markup for all option tags.
	 *
	 * @return string The markup for all option tags.
	 */
	private function get_options() {

		$options = '<option value="-1">' . esc_html__( 'Choose language', 'multilingual-press' ) . '</option>';

		$languages = $this->languages->get_all_languages();
		if ( $languages ) {
			$options = array_reduce( $languages, function ( $options, $language ) {

				if ( isset( $language->http_name, $language->english_name, $language->native_name ) ) {
					$options .= sprintf(
						'<option value="%2$s"%3$s>%1$s</option>',
						esc_html( "{$language->english_name}/{$language->native_name}" ),
						esc_attr( $language->http_name ),
						selected( $this->default_language, $language->http_name, false )
					);
				}

				return $options;
			}, $options );
		}

		return $options;
	}
}
