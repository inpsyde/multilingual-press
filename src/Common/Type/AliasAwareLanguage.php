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

		$this->is_rtl = ! empty( $data['is_rtl'] );

		$this->names = $this->get_names( $data );

		if ( isset( $data['priority'] ) && is_numeric( $data['priority'] ) ) {
			$this->priority = (int) $data['priority'];
		}
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
	 * * wp_locale:      WordPress locale representing the language.
	 * * none:           No text output (e.g,. for displaying the flag icon only).
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
			return strtok( $this->names['http_name'], '-' );
		}

		if ( 'language_long' === $output ) {
			return (string) $this->names['http_name'];
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
	 * Returns the complete set of language names and codes for the given language data.
	 *
	 * @param array $data Language data.
	 *
	 * @return string[] Language names and codes.
	 */
	private function get_names( array $data ): array {

		$names = [
			'english_name' => '',
			'native_name'  => '',
			'custom_name'  => $data['text'] ?? '',
			'text'         => '',
			'http_name'    => '',
			'wp_locale'    => '',
		];

		$names = array_replace( $names, array_intersect_key( $data, $names ) );

		return array_map( 'strval', $names );
	}
}
