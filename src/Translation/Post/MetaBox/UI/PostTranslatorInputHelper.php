<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI
 * @since   3.0.0
 */
class PostTranslatorInputHelper {

	const NAME_BASE = 'mlp_translation_data';

	const ID_BASE = 'mlp-translation-data';

	/**
	 * @param int    $site_id
	 * @param string $name
	 *
	 * @return string
	 */
	public function field_name( int $site_id, string $name ): string {

		return self::NAME_BASE . "[{$site_id}][" . esc_attr( $name ) . ']';
	}

	/**
	 * @param int    $site_id
	 * @param string $name
	 *
	 * @return string
	 */
	public function field_id( int $site_id, string $name ): string {

		return self::ID_BASE . "-{$site_id}-" . esc_attr( $name );
	}

}