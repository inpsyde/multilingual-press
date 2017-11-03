<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Type;

use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;

/**
 * Language data type implementation aware of language name aliases (e.g., "lang" for "language_short").
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
final class AliasAwareLanguage implements Language {

	/**
	 * @var int
	 */
	private $id = 0;

	/**
	 * @var bool
	 */
	private $is_rtl;

	/**
	 * @var string[]
	 */
	private $names;

	/**
	 * @var int
	 */
	private $priority = 1;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Language data.
	 */
	public function __construct( array $data ) {

		if ( isset( $data[ Language::ID ] ) && is_numeric( $data[ Language::ID ] ) ) {
			$this->id = (int) $data[ Language::ID ];
		}

		$this->is_rtl = (bool) ( $data[ Language::IS_RTL ] ?? false );

		$this->names = $this->get_names( $data );

		if ( isset( $data[ Language::PRIORITY ] ) && is_numeric( $data[ Language::PRIORITY ] ) ) {
			$this->priority = (int) $data[ Language::PRIORITY ];
		}
	}

	/**
	 * Checks if a value with the given name exists.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value.
	 *
	 * @return bool Whether or not a value with the given name exists.
	 */
	public function offsetExists( $name ) {

		static $names;
		if ( ! $names ) {
			$names = [
				Language::CUSTOM_NAME,
				Language::ENGLISH_NAME,
				Language::HTTP_CODE,
				Language::ID,
				Language::ISO_639_1_CODE,
				Language::ISO_639_2_CODE,
				Language::IS_RTL,
				Language::LOCALE,
				Language::NATIVE_NAME,
				Language::PRIORITY,
			];
		}

		return in_array( (string) $name, $names, true );
	}

	/**
	 * Returns the value with the given name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value.
	 *
	 * @return mixed The value with the given name.
	 */
	public function offsetGet( $name ) {

		$name = (string) $name;

		if ( Language::ID === $name ) {
			return $this->id;
		}

		if ( Language::IS_RTL === $name ) {
			return $this->is_rtl;
		}

		if ( Language::PRIORITY === $name ) {
			return $this->priority;
		}

		if ( Language::ID === $name ) {
			return (int) $this->names[ Language::ID ] ?? 0;
		}

		return (string) ( $this->names[ $name ] ?? '' );
	}

	/**
	 * Stores the given value with the given name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name  The name of a value.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 */
	public function offsetSet( $name, $value ) {

	}

	/**
	 * Removes the value with the given name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value.
	 *
	 * @return void
	 */
	public function offsetUnset( $name ) {

	}

	/**
	 * Returns the ID of the language.
	 *
	 * @since 3.0.0
	 *
	 * @return int Language ID.
	 */
	public function id(): int {

		return $this->id;
	}

	/**
	 * Checks if the language is written right-to-left (RTL).
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the language is written right-to-left (RTL).
	 */
	public function is_rtl(): bool {

		return $this->is_rtl;
	}

	/**
	 * Returns the language name (or code) according to the given argument.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Optional. Output type. Defaults to 'native'.
	 *                       Possible values:
	 *                       - native:         Native name of the language (default, e.g., "Deutsch" for German).
	 *                       - english:        English name of the language.
	 *                       - custom:         Language name as defined in the site settings.
	 *                       - http:           HTTP code of the language (e.g., "de-DE").
	 *                       - language_long:  Alias for "http".
	 *                       - language_short: First part of "http" (e.g., "de" for "de-DE").
	 *                       - lang:           Alias for "language_short".
	 *                       - locale:         WordPress locale representing the language.
	 *                       - none:           No text output.
	 *
	 * @return string Language name (or code) according to the given argument.
	 */
	public function name( string $output = Language::NATIVE_NAME ): string {

		switch ( $output ) {
			case Language::CODE_SHORT:
			case SiteSettingsRepository::KEY_LANGUAGE:
				return strtok( $this->names[ Language::HTTP_CODE ], '-' );

			case 'http':
			case Language::CODE_LONG:
				return $this->names[ Language::HTTP_CODE ];

			case Language::NONE:
				return '';
		}

		$keys = array_unique( [
			$output,
			"{$output}_name",
			Language::NATIVE_NAME,
			Language::ENGLISH_NAME,
		] );

		foreach ( $keys as $key ) {
			if ( ! empty( $this->names[ $key ] ) ) {
				return $this->names[ $key ];
			}
		}

		return '';
	}

	/**
	 * Returns the language priority.
	 *
	 * @since 3.0.0
	 *
	 * @return int Language priority.
	 */
	public function priority(): int {

		return $this->priority;
	}

	/**
	 * Returns the language data in array form.
	 *
	 * @since 3.0.0
	 *
	 * @return array Language data.
	 */
	public function to_array(): array {

		return array_merge( $this->names, [
			Language::ID       => $this->id,
			Language::IS_RTL   => $this->is_rtl,
			Language::PRIORITY => $this->priority,
		] );
	}

	/**
	 * Returns the complete set of language names and codes for the given language data.
	 *
	 * @param array $data Language data.
	 *
	 * @return string[] Language names and codes.
	 */
	private function get_names( array $data ): array {

		$names = [
			Language::CUSTOM_NAME    => $data[ SiteSettingsRepository::KEY_ALTERNATIVE_LANGUAGE_TITLE ] ?? '',
			Language::ENGLISH_NAME   => '',
			Language::HTTP_CODE      => '',
			Language::ID             => 0,
			Language::ISO_639_1_CODE => '',
			Language::ISO_639_2_CODE => '',
			Language::LOCALE         => '',
			Language::NATIVE_NAME    => '',
		];

		$names = array_replace( $names, array_intersect_key( $data, $names ) );

		return array_map( 'strval', $names );
	}
}
