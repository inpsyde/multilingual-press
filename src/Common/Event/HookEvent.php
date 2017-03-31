<?php # -*- coding: utf-8 -*-

declare( strict_types=1 );

namespace Inpsyde\MultilingualPress\Common\Event;

/**
 * Event implementation based on WordPress hooks.
 *
 * This is actually helper class to deal with arbitrary callbacks attached to WP hooks, with a nicer OOP fluent API,
 * and a simplified way to pass additional context to hooked callbacks and without worrying about passing the proper
 * number of arguments to `add_action`, `add_filter`.
 *
 * Example:
 *
 * <code>
 * HookEvent::for( 'save_post' )
 *     ->listen( function( MyLogger $logger, int $post_id, \WP_Post $post, bool $update ) {
 *          $logger->log( sprintf( "The post %s have been %s.", $post->post_title, $update ? 'updated': 'saved' ) );
 *     }, $logger )
 *     ->listen( function( MyPostProcessor $processor, int $post_id, \WP_Post $post ) {
 *         $processor->process( $post );
 *     }, $processor );
 * </code>
 *
 * @package Inpsyde\MultilingualPress\Common\Event
 * @since   3.0.0
 */
final class HookEvent implements Event {

	/**
	 * @var HookEvent[]
	 */
	private static $events = [];

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $context = [];

	/**
	 * @var \SplObjectStorage
	 */
	private $callbacks;

	/**
	 * Create an event instance for a WordPress hook and setup its context to hook arguments when the hook is fired.
	 *
	 * @param string $hook
	 *
	 * @return HookEvent
	 */
	public static function for( string $hook ) {

		if ( array_key_exists( $hook, self::$events ) ) {
			return self::$events[ $hook ];
		}

		self::$events[ $hook ] = new static( $hook );

		/*
		 * Using a filter allow us to be more flexible, because add_filter works for actions as well.
		 * Normally we should have an action, but sometimes WordPress or third party code provides only a filter
		 * in the place where an hook is needed.
		 * The hooked callback returns the 1st argument, which will have no effect on filters and is ignored on actions.
		 */
		add_filter( $hook, function ( ...$args ) use ( $hook ) {

			$return = self::$events[ $hook ]->dispatch( $args );
			unset( self::$events[ $hook ] );

			return $return;

		}, PHP_INT_MAX, PHP_INT_MAX ); // very late, with all arguments

		return self::$events[ $hook ];
	}

	/**
	 * Constructor. Sets properties;
	 *
	 * @param string $name
	 * @param array  $context
	 */
	public function __construct( string $name, array $context = [] ) {

		$this->name      = $name;
		$this->context   = $context;
		$this->callbacks = new \SplObjectStorage();
	}

	/**
	 * Event name.
	 *
	 * @return string
	 */
	public function name(): string {

		return $this->name;
	}

	/**
	 * Event context.
	 *
	 * @return array
	 */
	public function context(): array {

		return $this->context;
	}

	/**
	 * Register a callback to be called when the event is fired.
	 *
	 * @param callable $callback
	 * @param array    $args     Arguments to be passed to the callback.
	 *                           The event context is appended to given arguments.
	 *
	 * @return Event Itself.
	 */
	public function listen( callable $callback, ...$args ): Event {

		// Unfortunately \Closure::fromCallable() is PHP 7.1+
		$object = $callback;
		if ( ! is_object( $callback ) ) {
			$object = function ( ...$args ) use ( $callback ) {

				return $callback( ...$args );
			};
		}

		$this->callbacks->attach( $object, $args );

		return $this;
	}

	/**
	 * @param array $context
	 *
	 * @return mixed
	 */
	private function dispatch( array $context ) {

		$this->context = $context;

		$hook_param = $context ? reset( $context ) : null;

		$this->callbacks->rewind();

		while ( $this->callbacks->valid() ) {

			/** @var callable $callback */
			$callback = $this->callbacks->current();

			/** @var array $callback_args */
			$callback_args = $this->callbacks->getInfo();

			if ( $context ) {
				$callback_args = array_merge( $callback_args, $context );
			}

			$callback( ...$callback_args );

			$this->callbacks->next();
		}

		unset( $this->callbacks );

		return $hook_param;
	}
}