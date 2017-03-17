<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Nonce;

/**
 * WordPress-specific nonce implementation.
 *
 * @package Inpsyde\MultilingualPress\Common\Nonce
 * @since   3.0.0
 */
final class WPNonce implements Nonce {

	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string  $action  Nonce action.
	 * @param Context $context Optional. Nonce context object. Defaults to null.
	 */
	public function __construct( string $action, Context $context = null ) {

		$this->action = $action;

		$this->context = $context;
	}

	/**
	 * Returns the nonce value.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce value.
	 */
	public function __toString(): string {

		return (string) wp_create_nonce( $this->get_hash() );
	}

	/**
	 * Returns the nonce action.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce action.
	 */
	public function action(): string {

		return $this->action;
	}

	/**
	 * Checks if the nonce is valid with respect to the current context.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the nonce is valid.
	 */
	public function is_valid(): bool {

		if ( ! $this->context ) {
			$this->context = new OriginalRequestContext();
		}

		if ( ! isset( $this->context[ $this->action ] ) ) {
			return false;
		}

		$nonce = $this->context[ $this->action ];
		if ( ! is_string( $nonce ) ) {
			return false;
		}

		return (bool) wp_verify_nonce( $nonce, $this->get_hash() );
	}

	/**
	 * Returns a hash for the current action and site ID.
	 *
	 * @return string The hash for the current action and site ID.
	 */
	private function get_hash() {

		return (string) wp_hash( $this->action . get_current_blog_id(), 'nonce' );
	}
}
