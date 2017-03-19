<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service\Exception;

/**
 * Exception to be thrown when an action is to be performed with no container available.
 *
 * @package Inpsyde\MultilingualPress\Service\Exception
 * @since   3.0.0
 */
class ContainerNotSet extends \Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $action Optional. Action to be performed. Defaults to 'register'.
	 *
	 * @return ContainerNotSet Exception object.
	 */
	public static function for_action( string $action = 'register' ): ContainerNotSet {

		return new static( sprintf(
			'Cannot %1$s. No container available.',
			$action
		) );
	}
}
