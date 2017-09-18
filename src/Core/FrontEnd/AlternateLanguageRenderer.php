<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\FrontEnd;

/**
 * Interface for all alternate language renderer implementations.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd
 * @since   3.0.0
 */
interface AlternateLanguageRenderer {

	/**
	 * Output type.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const TYPE_HTTP_HEADER = 1;

	/**
	 * Output type.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const TYPE_HTML_LINK_TAG = 2;

	/**
	 * Renders all available alternate languages.
	 *
	 * @since 3.0.0
	 *
	 * @param array ...$args Optional arguments.
	 *
	 * @return void
	 */
	public function render( ...$args );

	/**
	 * Returns the output type.
	 *
	 * @since 3.0.0
	 *
	 * @return int The output type.
	 */
	public function type(): int;
}
