<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Exception;

use Exception;

/**
 * Exception to be thrown when a module that has already been registered is to be manipulated.
 *
 * @package Inpsyde\MultilingualPress\Module\Exception
 * @since   3.0.0
 */
class ModuleAlreadyRegisteredException extends Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id     Module ID.
	 * @param string $action Optional. Action to be performed. Defaults to 'register'.
	 *
	 * @return static Exception object.
	 */
	public static function for_id( $id, $action = 'register' ) {

		return new static( sprintf(
			'Cannot %2$s "%1$s". There already is a module with this ID.',
			$id,
			$action
		) );
	}
}
