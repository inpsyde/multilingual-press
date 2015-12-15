<?php # -*- coding: utf-8 -*-

/**
 * A very simple implementation to provide nonces and validation.
 */
class Inpsyde_Nonce_Validator implements Inpsyde_Nonce_Validator_Interface {

	/**
	 * Current nonce action.
	 *
	 * @var string
	 */
	private $action;

	/**
	 * @var string
	 */
	private $base;

	/**
	 * @var int
	 */
	private $blog_id;

	/**
	 * Current nonce name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $type;

	/**
	 * Constructor.
	 *
	 * @param string $base
	 * @param int    $blog_id Set a blog id to restrict the nonce to one site.
	 */
	public function __construct( $base, $blog_id = 0 ) {

		$this->base = (string) $base;

		$this->blog_id = absint( $blog_id );

		$this->action = $this->base . '_nonce_action_' . $this->blog_id;
		$this->name   = $this->base . '_nonce_name_' . $this->blog_id;

		if ( 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			$this->type = INPUT_GET;
		} elseif ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$this->type = INPUT_POST;
		}
	}

	/**
	 * Returns the nonce field name.
	 *
	 * @return string
	 */
	public function get_name() {

		return $this->name;
	}

	/**
	 * Returns the nonce action.
	 *
	 * @return string
	 */
	public function get_action() {

		return $this->action;
	}

	/**
	 * Checks if the current request is valid.
	 *
	 * @return bool
	 */
	public function is_valid() {

		if ( ! isset( $this->type ) ) {
			return false;
		}

		if ( 0 === $this->blog_id ) {
			$blog_id = 0;
		} else {
			$blog_id = get_current_blog_id();
		}

		$name = $this->base . '_nonce_name_' . $blog_id;

		$nonce = filter_input( $this->type, $name );

		return wp_verify_nonce( $nonce, $this->action );
	}
}
