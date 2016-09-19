<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages;

/**
 * Interface for all translation data access implementations.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages
 * @since   3.0.0
 */
interface Translations {

	/**
	 * Returns the translations.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] Array with HTTP language codes as keys and URLs as values.
	 */
	public function to_array();
}
