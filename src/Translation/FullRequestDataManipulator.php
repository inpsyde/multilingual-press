<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation;

/**
 * Request data manipulator implementation for the full request data.
 *
 * @package Inpsyde\MultilingualPress\Translation
 * @since   3.0.0
 */
final class FullRequestDataManipulator implements RequestDataManipulator {

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
	public function __construct( string $request_method = RequestDataManipulator::METHOD_POST ) {

		$this->request_method = RequestDataManipulator::METHOD_POST === strtoupper( $request_method )
			? RequestDataManipulator::METHOD_POST
			: RequestDataManipulator::METHOD_GET;
	}

	/**
	 * Removes all data from the request global.
	 *
	 * @since 3.0.0
	 *
	 * @return int Number of cleared elements.
	 */
	public function clear_data(): int {

		if ( empty( $GLOBALS["_{$this->request_method}"] ) ) {
			return 0;
		}

		$this->storage = $GLOBALS["_{$this->request_method}"];

		$_REQUEST = array_diff_key( $_REQUEST, $this->storage );

		$GLOBALS["_{$this->request_method}"] = [];

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

		$_REQUEST = array_merge( $_REQUEST, $this->storage );

		$GLOBALS["_{$this->request_method}"] = $this->storage;

		$this->storage = [];

		return count( $GLOBALS["_{$this->request_method}"] );
	}
}
