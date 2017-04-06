<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class TranslationViewInjector {

	/**
	 * @param string   $position
	 * @param callable $callback
	 * @param int      $priority
	 */
	public static function inject_in_view( string $position, callable $callback, int $priority = 10 ) {

		if ( ! in_array( $position, TranslationMetaboxView::POSITIONS, true ) ) {
			return;
		}

		add_action(
			TranslationMetaboxView::ACTION_RENDER_PREFIX . $position,
			function ( ...$args ) use ( $callback ) {

				ob_start();
				$return = $callback( ...$args );
				$buffer = ob_get_clean();

				if ( trim( $buffer ) ) {
					echo $buffer;
				} elseif ( is_string( $return ) ) {
					echo $return;
				}
			},
			$priority,
			4
		);

	}
}