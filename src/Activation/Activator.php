<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Activation;

/**
 * Interface for all activator implementations.
 *
 * @package Inpsyde\MultilingualPress\Activation
 * @since   3.0.0
 */
interface Activator {

	/**
	 * Takes care of pending plugin activation tasks.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not all pending tasks were taken care of successfully.
	 */
	public function handle_pending_activation(): bool;

	/**
	 * Performs anything to handle the plugin activation.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the activation was handled successfully.
	 */
	public function handle_activation(): bool;

	/**
	 * Registers the given callback.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Callback to be executed upon activation.
	 * @param bool     $prepend  Optional. Prepend instead of append to the callback queue? Defaults to false.
	 *
	 * @return Activator
	 */
	public function register_callback( callable $callback, bool $prepend = false ): Activator;
}
