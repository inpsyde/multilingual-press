<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox;

/**
 * Trait to be used (e.g., by UI implementations) to inject data into the post translation meta box view.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox
 * @since   3.0.0
 */
trait ViewInjection {

	/**
	 * Injects the output (or returned) data of the given callback into the post translation meta box view.
	 *
	 * @param callable $callback Callback rendering data for the meta box view.
	 * @param string   $position Optional. Position in the meta box view. Use the view constants. Defaults to bottom.
	 * @param int      $priority Optional. Priority for the callback. Defaults to 10.
	 *
	 * @return void
	 */
	private function inject_into_view(
		callable $callback,
		string $position = TranslationMetaBoxView::POSITION_BOTTOM,
		int $priority = 10
	) {

		if ( ! in_array( $position, TranslationMetaBoxView::POSITIONS, true ) ) {
			return;
		}

		add_action( TranslationMetaBoxView::ACTION_RENDER_PREFIX . $position, function ( ...$args ) use ( $callback ) {

			ob_start();

			$return = $callback( ...$args );

			$contents = ob_get_clean();
			if ( trim( $contents ) ) {
				echo $contents;

				return;
			}

			if ( is_string( $return ) ) {
				echo $return;
			}
		}, $priority, 5 );
	}
}
