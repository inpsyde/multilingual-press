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
	 * @return static
	 */
	public static function from_globals() {

		return new static(
			get_current_blog_id(),
			(array) ( $GLOBALS['_wp_switched_stack'] ?? [] )
		);
	}

	/**
	 * Restores the stored site state.
	 *
	 * @since 3.0.0
	 *
	 * @return int The current site ID.
	 */
	public function restore() {

		switch_to_blog( $this->site_id );

		$GLOBALS['_wp_switched_stack'] = $this->stack;

		$GLOBALS['switched'] = ! empty( $this->stack );

		return get_current_blog_id();
	}
}
