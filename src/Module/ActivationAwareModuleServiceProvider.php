<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module;

/**
 * Interface for all activation-aware module service provider implementations to be used for dependency management.
 *
 * @package Inpsyde\MultilingualPress\Module
 * @since   3.0.0
 */
interface ActivationAwareModuleServiceProvider extends ModuleServiceProvider {

	/**
	 * Executes the callback to be used in case this service provider's module is active.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the callback was executed.
	 */
	public function activate();
}
