<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Language data type implementation aware of language name aliases (e.g., "lang" for "language_short").
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
final class AliasAwareLanguage implements Language {

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

		// TODO: Introduce method/trait for normalizing language data (i.e., handle both types of constants as keys).

		$this->is_rtl = (bool) ( $data['is_rtl'] ?? false );

		$this->names = $this->get_names( $data );

		if ( isset( $data['priority'] ) && is_numeric( $data['priority'] ) ) {
			$this->priority = (int) $data['priority'];
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

		static $names = [
			'custom_name',
			'english_name',
			'http_code',
			'iso_639_1',
			'iso_639_2',
			'is_rtl',
			'locale',
			'native_name',
			'priority',
			'text',
		    'ID',
		];

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

		if ( 'is_rtl' === $name ) {
			return $this->is_rtl;
		}

		if ( 'priority' === $name ) {
			return $this->priority;
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
	 *
	 * TODO: Check/Adapt formatting to provide best possible readability for both inline and rendered documentation.
	 * Possible values:
	 *
	 * * native:         Native name of the language (default, e.g., "Deutsch" for German).
	 * * english:        English name of the language.
	 * * custom:         Language name as defined in the site settings.
	 * * text:           Alias for "custom".
	 * * http:           HTTP code of the language (e.g., "de-DE").
	 * * language_long:  Alias for "http".
	 * * language_short: First part of "http" (e.g., "de" for "de-DE").
	 * * lang:           Alias for "language_short".
	 * * locale:         WordPress locale representing the language.
	 * * none:           No text output.
	 *
	 * @return string Language name (or code) according to the given argument.
	 */
	public function name( string $output = 'native' ): string {

		if ( ! empty( $this->names[ $output ] ) ) {
			return (string) $this->names[ $output ];
		}

		if ( ! empty( $this->names["{$output}_name"] ) ) {
			return (string) $this->names["{$output}_name"];
		}

		if ( in_array( $output, [ 'language_short', 'lang' ], true ) ) {
			return strtok( $this->names['http_code'], '-' );
		}

		if ( 'language_long' === $output ) {
			return (string) $this->names['http_code'];
		}

		if ( 'none' === $output ) {
			return '';
		}

		// Since the given output type is either empty or invalid, return the native or English language name, if set.
		foreach ( [ 'native_name', 'english_name' ] as $key ) {
			if ( ! empty( $this->names[ $key ] ) ) {
				return (string) $this->names[ $key ];
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
			'is_rtl'   => $this->is_rtl,
			'priority' => $this->priority,
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
			'custom_name'  => $data['text'] ?? '',
			'english_name' => '',
			'http_code'    => '',
			'iso_639_1'    => '',
			'iso_639_2'    => '',
			'native_name'  => '',
			'text'         => '',
			'locale'       => '',
		    'ID'           => ''
		];

		$names = array_replace( $names, array_intersect_key( $data, $names ) );

		return array_map( 'strval', $names );
	}
}
