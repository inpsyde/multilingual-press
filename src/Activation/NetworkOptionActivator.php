<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Activation;

/**
 * Activator implementation using a network option.
 *
 * @package Inpsyde\MultilingualPress\Activation
 * @since   3.0.0
 */
final class NetworkOptionActivator implements Activator {

	/**
	 * Option name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const OPTION = 'multilingualpress.activation';

	/**
	 * @var callable[]
	 */
	private $callbacks = [];

	/**
	 * Takes care of pending plugin activation tasks.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not there were pending plugin activation tasks to take care of.
	 */
	public function handle_pending_activation(): bool {

		if ( ! get_network_option( null, self::OPTION ) ) {
			return false;
		}

		foreach ( $this->callbacks as $callback ) {
			$callback();
		}

		delete_network_option( null, self::OPTION );

		return true;
	}

	/**
	 * Performs anything to handle the plugin activation.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the activation was handled successfully.
	 */
	public function handle_activation(): bool {

		update_network_option( null, self::OPTION, true );

		return (bool) get_network_option( null, self::OPTION );
	}

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
	public function register_callback( callable $callback, bool $prepend = false ): Activator {

		if ( $prepend ) {
			array_unshift( $this->callbacks, $callback );
		} else {
			$this->callbacks[] = $callback;
		}

		return $this;
	}
}
