<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Integration;

/**
 * Interface for all integration controllers.
 *
 * @package Inpsyde\MultilingualPress\Integration
 * @since   3.0.0
 */
interface Integration {

	/**
	 * Integrates some (possibly external) service with MultilingualPress.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the service was integrated successfully.
	 */
	public function integrate(): bool;
}
