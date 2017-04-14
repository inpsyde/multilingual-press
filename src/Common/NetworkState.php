<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common;

/**
 * Storage for the (switched) state of the network.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
class NetworkState {

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * @var int[]
	 */
	private $stack;

	/**
	 * @var bool
	 */
	private $is_globals = false;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $site_id Site ID.
	 * @param array $stack   Optional. Site ID stack. Defaults to empty array.
	 */
	public function __construct( int $site_id, array $stack = [] ) {

		$this->site_id = $site_id;

		$this->stack = $stack;
	}

	/**
	 * Returns a new instance for the global site ID and switched stack.
	 *
	 * @since 3.0.0
	 *
	 * @return NetworkState
	 */
	public static function from_globals(): NetworkState {

		$instance = new static(
			get_current_blog_id(),
			(array) ( $GLOBALS['_wp_switched_stack'] ?? [] )
		);

		$instance->is_globals = true;

		return $instance;
	}

	/**
	 * Restores the stored site state.
	 *
	 * @since 3.0.0
	 *
	 * @return int The current site ID.
	 */
	public function restore(): int {

		// If class status is not initialized from globals, we don't affect globals on restore.
		if ( ! $this->is_globals ) {
			return $this->site_id;
		}

		$current = get_current_blog_id();

		// If current site is the same of initial site and the stack is identical, there's nothing we have to do.
		if ( $current !== $this->site_id || ( $GLOBALS['_wp_switched_stack'] ?? null ) !== $this->stack ) {

			switch_to_blog( $this->site_id );

			$GLOBALS['_wp_switched_stack'] = $this->stack;

			$GLOBALS['switched'] = ! empty( $this->stack );

			$current = $this->site_id;

		}

		return $current;
	}
}
