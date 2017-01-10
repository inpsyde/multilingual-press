<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\UserAdminLanguage;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Setting\User\UserSettingViewModel;
use WP_User;

/**
 * User admin language user setting.
 *
 * @package Inpsyde\MultilingualPress\Module\UserAdminLanguage
 * @since   3.0.0
 */
final class Setting implements UserSettingViewModel {

	/**
	 * @var LanguageRepository
	 */
	private $language_repository;

	/**
	 * @var string
	 */
	private $meta_key;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string             $meta_key            User meta key.
	 * @param Nonce              $nonce               Nonce object.
	 * @param LanguageRepository $language_repository Language repository object.
	 */
	public function __construct( $meta_key, Nonce $nonce, LanguageRepository $language_repository ) {

		$this->meta_key = (string) $meta_key;

		$this->nonce = $nonce;

		$this->language_repository = $language_repository;
	}

	/**
	 * Returns the markup for the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_User $user User object.
	 *
	 * @return string The markup for the user setting.
	 */
	public function markup( WP_User $user ) {

		$languages = get_available_languages();
		if ( ! $languages ) {
			return esc_html__( 'No languages available.', 'multilingual-press' );
		}

		// Add English manually, because it won't get added by WordPress itself.
		$languages[] = 'en_US';

		return sprintf(
			'<select name="%2$s" id="%2$s" autocomplete="off">%1$s</select>%3$s',
			$this->get_language_options( $languages, $this->language_repository->get_user_language( $user->ID ) ),
			esc_attr( $this->meta_key ),
			\Inpsyde\MultilingualPress\nonce_field( $this->nonce )
		);
	}

	/**
	 * Returns the title of the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the user setting.
	 */
	public function title() {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Your preferred backend language', 'multilingual-press' ),
			esc_attr( $this->meta_key )
		);
	}

	/**
	 * Returns the HTML of the option elements according to the given available languages and the selected one.
	 *
	 * @param string[] $language_files An array of all file names for all available languages.
	 * @param string   $selected       Currently selected language value.
	 *
	 * @return string The HTML of the option element according to the given arguments.
	 */
	private function get_language_options( array $language_files, $selected ) {

		$options = array_reduce( $language_files, function ( array $options, $language_file ) use ( $selected ) {

			$language_code = basename( $language_file, '.mo' );

			$language = 'en_US' === $language_code
				? __( 'English', 'multilingual-press' )
				: format_code_lang( $language_code );

			$options[ $language ] = $this->get_language_option( $language, $language_code, $selected );

			return $options;
		}, [ $this->get_language_option( __( 'Site Language', 'multilingual-press' ), '', $selected ) ] );

		// Order by name.
		uksort( $options, 'strnatcasecmp' );

		return implode( '', $options );
	}

	/**
	 * Returns the HTML of the option element according to the given arguments.
	 *
	 * @param string $text     Option text.
	 * @param string $value    Option value.
	 * @param string $selected Currently selected option value.
	 *
	 * @return string The HTML of the option element according to the given arguments.
	 */
	private function get_language_option( $text, $value, $selected ) {

		return sprintf(
			'<option value="%2$s"%3$s>%1$s</option>',
			esc_html( $text ),
			esc_attr( $value ),
			selected( $selected, $value, false )
		);
	}
}
