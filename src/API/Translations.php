<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Common\Type\Translation;
use Inpsyde\MultilingualPress\Translation\Translator;

/**
 * Interface for all translations API implementations.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
interface Translations {

	/**
	 * Returns all translation according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Optional. Arguments required to fetch the translations. Defaults to empty array.
	 *
	 * @return Translation[] An array with site IDs as keys and Translation objects as values.
	 */
	public function get_translations( array $args = [] );

	/**
	 * Returns the unfiltered translations.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] Array with HTTP language codes as keys and URLs as values.
	 */
	public function get_unfiltered_translations();

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
	public function register_translator( Translator $translator, $type );
}
