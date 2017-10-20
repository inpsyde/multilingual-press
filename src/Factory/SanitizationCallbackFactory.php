<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Factory;

/**
 * Static factory for diverse sanitization callbacks.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
class SanitizationCallbackFactory {

	/**
	 * Returns a callback that sanitizes and returns the given value.
	 *
	 * @since 3.0.0
	 *
	 * @param array $default Optional. Default return value. Defaults to an empty array.
	 *
	 * @return \Closure Sanitization callback.
	 */
	public static function sanitize_array( array $default = [] ): \Closure {

		/**
		 * Sanitizes and returns the given value.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed $value Value to be sanitized.
		 *
		 * @return array Sanitized value.
		 */
		return function ( $value ) use ( $default ): array {

			return is_array( $value ) ? $value : $default;
		};
	}

	/**
	 * Returns a callback that sanitizes and returns the given value.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Sanitization callback.
	 *
	 * @return \Closure Sanitization callback.
	 */
	public static function sanitize_array_elements( callable $callback ): \Closure {

		/**
		 * Sanitizes and returns the given value.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed $value Value to be sanitized.
		 *
		 * @return array Sanitized value.
		 */
		return function ( $value ) use ( $callback ) : array {

			return array_map( $callback, $value );
		};
	}

	/**
	 * Returns a callback that sanitizes and returns the given value.
	 *
	 * @since 3.0.0
	 *
	 * @return \Closure Sanitization callback.
	 */
	public static function sanitize_bool(): \Closure {

		/**
		 * Sanitizes and returns the given value.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed $value Value to be sanitized.
		 *
		 * @return bool Sanitized value.
		 */
		return function ( $value ): bool {

			return (bool) $value;
		};
	}

	/**
	 * Returns a callback that sanitizes and returns the given value.
	 *
	 * @since 3.0.0
	 *
	 * @return \Closure Sanitization callback.
	 */
	public static function sanitize_numeric_id(): \Closure {

		/**
		 * Sanitizes and returns the given value.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed $value Value to be sanitized.
		 *
		 * @return int Sanitized value.
		 */
		return function ( $value ): int {

			return absint( $value );
		};
	}

	/**
	 * Returns a callback that sanitizes and returns the given value.
	 *
	 * @since 3.0.0
	 *
	 * @param string $default Optional. Default return value. Defaults to an empty string.
	 *
	 * @return \Closure Sanitization callback.
	 */
	public static function sanitize_string( string $default = '' ): \Closure {

		/**
		 * Sanitizes and returns the given value.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed $value Value to be sanitized.
		 *
		 * @return string Sanitized value.
		 */
		return function ( $value ) use ( $default ) : string {

			if ( is_string( $value ) ) {
				return $value;
			}

			if (
				is_scalar( $value )
				|| ( is_object( $value ) && method_exists( $value, '__toString' ) )
			) {
				return (string) $value;
			}

			return $default;
		};
	}
}
