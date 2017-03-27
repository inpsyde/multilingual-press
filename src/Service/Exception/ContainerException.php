<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service\Exception;

/**
 * Exception base class for all exceptions thrown by the container.
 *
 * This is necessary to be able to catch all exceptions thrown in the module in one go.
 * Moreover, this will make future compliance with PSR-11 easier, with pretty much no code necessary.
 *
 * @package Inpsyde\MultilingualPress\Service\Exception
 * @since   3.0.0
 */
class ContainerException extends \Exception {

}
