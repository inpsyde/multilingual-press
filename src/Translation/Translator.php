<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Translation;

/**
 * Interface for all translator implementations.
 *
 * @package Inpsyde\MultilingualPress\Translation
 * @since   3.0.0
 */
interface Translator {

	/**
	 * Returns the translation data for the given site, according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $site_id Site ID.
	 * @param array $args    Optional. Arguments required to fetch translation. Defaults to empty array.
	 *
	 * @return array Translation data.
	 */
	public function get_translation( $site_id, array $args = [] );
}
