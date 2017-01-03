<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

/**
 * Trait for all context-aware filter implementations.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
trait ContextAwareFilter {

	/**
	 * @var int
	 */
	private $accepted_args;

	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * @var string
	 */
	private $hook;

	/**
	 * @var int
	 */
	private $priority;

	/**
	 * Returns the number of accepted arguments.
	 *
	 * @since 3.0.0
	 *
	 * @return int The number of accepted arguments.
	 */
	public function accepted_args() {

		return (int) ( $this->accepted_args ?: Filter::DEFAULT_ACCEPTED_ARGS );
	}

	/**
	 * Removes the filter.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook     Optional. Hook name. Defaults to empty string.
	 * @param int    $priority Optional. Callback priority. Defaults to 10.
	 *
	 * @return bool Whether or not the filter was removed successfully.
	 */
	public function disable( $hook = '', $priority = Filter::DEFAULT_PRIORITY ) {

		if ( ! $this->callback ) {
			return false;
		}

		$hook = $hook ?: $this->hook();

		if ( has_filter( $hook, $this->callback ) ) {
			remove_filter( $hook, $this->callback, $priority ?: $this->priority() );

			return true;
		};

		return false;
	}

	/**
	 * Adds the filter.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook          Optional. Hook name. Defaults to empty string.
	 * @param int    $priority      Optional. Callback priority. Defaults to 10.
	 * @param int    $accepted_args Optional. Number of accepted arguments. Defaults to 1.
	 *
	 * @return bool Whether or not the filter was added successfully.
	 */
	public function enable(
		$hook = '',
		$priority = Filter::DEFAULT_PRIORITY,
		$accepted_args = Filter::DEFAULT_ACCEPTED_ARGS
	) {

		if ( ! $this->callback ) {
			return false;
		}

		$hook = $hook ?: $this->hook();

		if ( has_filter( $hook, $this->callback ) ) {
			return false;
		};

		add_filter( $hook, $this->callback, $priority ?: $this->priority(), $accepted_args ?: $this->accepted_args() );

		return true;
	}

	/**
	 * Returns the hook name.
	 *
	 * @since 3.0.0
	 *
	 * @return string The hook name.
	 */
	public function hook() {

		return (string) $this->hook;
	}

	/**
	 * Returns the callback priority.
	 *
	 * @since 3.0.0
	 *
	 * @return int The callback priority.
	 */
	public function priority() {

		return (int) ( $this->priority ?: Filter::DEFAULT_PRIORITY );
	}
}
