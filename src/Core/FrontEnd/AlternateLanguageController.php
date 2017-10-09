<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\FrontEnd;

/**
 * Alternate language controller.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd
 * @since   3.0.0
 */
class AlternateLanguageController {

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_TYPE = 'multilingualpress.hreflang_type';

	/**
	 * @var int
	 */
	private $type;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		/**
		 * Filters the output type for the hreflang links.
		 *
		 * The type is a bitmask with possible (partial) values available as constants on the renderer interface.
		 *
		 * @since 2.7.0
		 *
		 * @param int $hreflang_type The output type for the hreflang links.
		 */
		$this->type = absint( apply_filters( self::FILTER_TYPE, AlternateLanguageRenderer::TYPE_HTTP_HEADER ) );
	}

	/**
	 * Registers the given renderer according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param AlternateLanguageRenderer $renderer      Renderer object.
	 * @param string                    $action        Action name.
	 * @param int                       $priority      Optional. Priority. Defaults to 10.
	 * @param int                       $accepted_args Optional. Number of accepted args. Defaults to 1.
	 *
	 * @return bool Whether or not the renderer was registered successfully.
	 */
	public function register_renderer(
		AlternateLanguageRenderer $renderer,
		string $action,
		int $priority = 10,
		int $accepted_args = 1
	): bool {

		if ( ! ( $this->type & $renderer->type() ) ) {
			return false;
		}

		add_action( $action, function ( ...$args ) use ( $renderer ) {

			if ( ! is_paged() ) {
				$renderer->render( ...$args );
			}
		}, $priority, $accepted_args );

		return true;
	}
}
