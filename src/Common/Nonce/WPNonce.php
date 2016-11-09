<?php # -*- coding: utf-8 -*-

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
	 * @var string
	 */
	private $action_hash;

	/**
	 * @var Context
	 */
	private $context;

	/**
	 * @var string
	 */
	private $nonce;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string  $action  Nonce action.
	 * @param Context $context Optional. Nonce context object. Defaults to null.
	 */
	public function __construct( $action, Context $context = null ) {

		$this->action = (string) $action;

		$this->context = $context;

		$this->action_hash = (string) wp_hash( $this->action . get_current_blog_id(), 'nonce' );

		$this->nonce = (string) wp_create_nonce( $this->action_hash );
	}

	/**
	 * Returns the nonce value.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce value.
	 */
	public function __toString() {

		return $this->nonce;
	}

	/**
	 * Returns the nonce action.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce action.
	 */
	public function action() {

		return $this->action;
	}

	/**
	 * Checks if the nonce is valid with respect to the current context.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the nonce is valid.
	 */
	public function is_valid() {

		if ( ! $this->context ) {
			$this->context = new RequestContext();
		}

		if ( ! isset( $this->context[ $this->action ] ) ) {
			return false;
		}

		$nonce = $this->context[ $this->action ];
		if ( ! is_string( $nonce ) ) {
			return false;
		}

		return (bool) wp_verify_nonce( $nonce, $this->action_hash );
	}
}
