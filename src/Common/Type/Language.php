<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Type;

use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

/**
 * Interface for all language data type implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
interface Language extends \ArrayAccess {

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const CODE_LONG = 'language_long';

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const CODE_SHORT = 'language_short';

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const CUSTOM_NAME = LanguagesTable::COLUMN_CUSTOM_NAME;

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ENGLISH_NAME = LanguagesTable::COLUMN_ENGLISH_NAME;

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const HTTP_CODE = LanguagesTable::COLUMN_HTTP_CODE;

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const IS_RTL = LanguagesTable::COLUMN_RTL;

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ISO_639_1_CODE = LanguagesTable::COLUMN_ISO_639_1_CODE;

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ISO_639_2_CODE = LanguagesTable::COLUMN_ISO_639_2_CODE;

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const LOCALE = LanguagesTable::COLUMN_LOCALE;

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NATIVE_NAME = LanguagesTable::COLUMN_NATIVE_NAME;

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PRIORITY = LanguagesTable::COLUMN_PRIORITY;

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NONE = 'none';

	/**
	 * Property name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ID = LanguagesTable::COLUMN_ID;

	/**
	 * Checks if the language is written right-to-left (RTL).
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the language is written right-to-left (RTL).
	 */
	public function is_rtl(): bool;

	/**
	 * Returns the language name (or code) according to the given argument.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Optional. Output type. Defaults to 'native'.
	 *
	 * @return string Language name (or code) according to the given argument.
	 */
	public function name( string $output = self::NATIVE_NAME ): string;

	/**
	 * Returns the language priority.
	 *
	 * @since 3.0.0
	 *
	 * @return int Language priority.
	 */
	public function priority(): int;

	/**
	 * Returns the language data in array form.
	 *
	 * @since 3.0.0
	 *
	 * @return array Language data.
	 */
	public function to_array(): array;
}
