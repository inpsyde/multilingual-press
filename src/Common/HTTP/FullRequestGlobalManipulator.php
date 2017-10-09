<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\HTTP;

/**
 * Request data manipulator implementation for the full request data.
 *
 * @package Inpsyde\MultilingualPress\Translation
 * @since   3.0.0
 */
final class FullRequestGlobalManipulator implements RequestGlobalsManipulator {

	/**
	 * @var string
	 */
	private $request_method;

	/**
	 * @var array
	 */
	private $storage = [];

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $request_method Optional. Request method. Defaults to RequestDataManipulator::METHOD_POST.
	 */
	public function __construct( string $request_method = RequestGlobalsManipulator::METHOD_POST ) {

		$this->request_method = RequestGlobalsManipulator::METHOD_POST === strtoupper( $request_method )
			? RequestGlobalsManipulator::METHOD_POST
			: RequestGlobalsManipulator::METHOD_GET;
	}

	/**
	 * Removes all data from the request globals.
	 *
	 * @since 3.0.0
	 *
	 * @return int Number of cleared elements.
	 */
	public function clear_data(): int {

		$name = "_{$this->request_method}";

		if ( empty( $GLOBALS[ $name ] ) ) {
			return 0;
		}

		$this->storage = $GLOBALS[ $name ];

		// @codingStandardsIgnoreLine
		$_REQUEST = array_diff_key( $_REQUEST, $this->storage );

		$GLOBALS[ $name ] = [];

		return count( $this->storage );
	}

	/**
	 * Restores all data from the storage.
	 *
	 * @since 3.0.0
	 *
	 * @return int Number of restored elements.
	 */
	public function restore_data(): int {

		if ( ! $this->storage ) {
			return 0;
		}

		// @codingStandardsIgnoreLine
		$_REQUEST = array_merge( $_REQUEST, $this->storage );

		$name = "_{$this->request_method}";

		$GLOBALS[ $name ] = $this->storage;

		$this->storage = [];

		return count( $GLOBALS[ $name ] );
	}
}
