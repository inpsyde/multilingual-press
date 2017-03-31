<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Event;

/**
 * Interface for all events.
 *
 * @package Inpsyde\MultilingualPress\Common\Event
 * @since   3.0.0
 */
interface Event {

	/**
	 * Event name.
	 *
	 * @return string
	 */
	public function name(): string;

	/**
	 * Event context.
	 *
	 * @return array
	 */
	public function context(): array;

	/**
	 * Register a callback to be called when the event is fired.
	 *
	 * @param callable $callback
	 * @param array    $args     Arguments to be passed to the callback.
	 *                           The event context and the even name are appended to this arguments.
	 *
	 * @return Event The event context is appended to given arguments.
	 */
	public function listen( callable $callback, ...$args ): Event;

}