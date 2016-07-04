<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Escaped URL data type.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
class EscapedURL implements URL {

	/**
	 * @var string
	 */
	private $url = '';

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $url URL source.
	 */
	public function __construct( $url ) {

		if (
			is_scalar( $url )
			|| ( is_object( $url ) && method_exists( $url, '__toString' ) )
		) {
			$this->url = (string) esc_url( (string) $url );
		}
	}

	/**
	 * Returns a new URL instance for the given URL source.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $url URL source.
	 *
	 * @return EscapedURL URL instance.
	 */
	public static function create( $url ) {

		return new self( $url );
	}

	/**
	 * Returns the URL string.
	 *
	 * @since 3.0.0
	 *
	 * @return string URL string.
	 */
	public function __toString() {

		return $this->url;
	}
}
