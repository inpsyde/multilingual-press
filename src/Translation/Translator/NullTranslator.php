<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Translator;

use Inpsyde\MultilingualPress\Translation\Translator;

/**
 * Null translator implementation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Translator
 * @since   3.0.0
 */
final class NullTranslator implements Translator {

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
	public function get_translation( int $site_id, array $args = [] ): array {

		return [];
	}
}
