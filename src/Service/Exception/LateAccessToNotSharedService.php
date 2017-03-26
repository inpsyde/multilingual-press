<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service\Exception;

/**
 * Exception to be thrown when a not shared value or factory callback is to be accessed on a bootstrapped container.
 *
 * @package Inpsyde\MultilingualPress\Service\Exception
 * @since   3.0.0
 */
class LateAccessToNotSharedService extends InvalidValueReadAccess {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the value or factory callback.
	 * @param string $action Optional. Action to be performed. Defaults to 'read'.
	 *
	 * @return LateAccessToNotSharedService Exception object.
	 */
	public static function for_name( string $name, string $action = 'read' ): LateAccessToNotSharedService {

		return new static( sprintf(
			'Cannot %2$s not shared "%1$s". The container has already been bootstrapped.',
			$name,
			$action
		) );
	}
}
