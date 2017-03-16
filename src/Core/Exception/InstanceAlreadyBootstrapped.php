<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Exception;

use Exception;

/**
 * Exception to be thrown an instance is to be bootstrapped that has already been bootstrapped.
 *
 * @package Inpsyde\MultilingualPress\Core\Exception
 * @since   3.0.0
 */
class InstanceAlreadyBootstrapped extends Exception {

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		parent::__construct( 'Cannot bootstrap an instance that has already been bootstrapped.' );
	}
}
