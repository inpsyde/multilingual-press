<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Null language implementation.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
final class NullLanguage implements Language {

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

		return false;
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

		return '';
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

		return 0;
	}

	/**
	 * Checks if the language is written right-to-left (RTL).
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the language is written right-to-left (RTL).
	 */
	public function is_rtl(): bool {

		return false;
	}

	/**
	 * Returns the language name (or code) according to the given argument.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Optional. Output type. Defaults to 'native'.
	 *
	 * @return string Language name (or code) according to the given argument.
	 */
	public function name( string $output = Language::NATIVE_NAME ): string {

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

		return 1;
	}

	/**
	 * Returns the language data in array form.
	 *
	 * @since 3.0.0
	 *
	 * @return array Language data.
	 */
	public function to_array(): array {

		return [
			Language::CUSTOM_NAME    => '',
			Language::ENGLISH_NAME   => '',
			Language::HTTP_CODE      => '',
			Language::ID             => 0,
			Language::IS_RTL         => false,
			Language::ISO_639_1_CODE => '',
			Language::ISO_639_2_CODE => '',
			Language::LOCALE         => '',
			Language::NATIVE_NAME    => '',
			Language::PRIORITY       => 1,
		];
	}
}
