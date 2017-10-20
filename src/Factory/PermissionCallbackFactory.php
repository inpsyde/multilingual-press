<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Factory;

/**
 * Static factory for diverse permission callbacks.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
class PermissionCallbackFactory {

	/**
	 * Returns a callback that checks if the current user has all of the given capabilities.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] ...$capabilities Capabilities required to get permission.
	 *
	 * @return \Closure Callback that checks if the current user has all of the given capabilities.
	 */
	public static function current_user_can( string ...$capabilities ) : \Closure {

		/**
		 * Checks if the current user has specific capabilities.
		 *
		 * @since 3.0.0
		 *
		 * @return bool Whether or not the current user has specific capabilities.
		 */
		return function () use ( $capabilities ): bool {

			foreach ( $capabilities as $capability ) {
				if ( ! current_user_can( $capability ) ) {
					return false;
				}
			}

			return true;
		};
	}

	/**
	 * Returns a callback that checks if the current user has all of the given capabilities in the site with the given
	 * ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int      $site_id         Site ID.
	 * @param string[] ...$capabilities Capabilities required to get permission.
	 *
	 * @return \Closure Callback that checks if the current user has all of the given capabilities.
	 */
	public static function current_user_can_for_site( int $site_id, string ...$capabilities ) : \Closure {

		/**
		 * Checks if the current user has specific capabilities.
		 *
		 * @since 3.0.0
		 *
		 * @return bool Whether or not the current user has specific capabilities.
		 */
		return function () use ( $site_id, $capabilities ) {

			switch_to_blog( $site_id );

			$current_user_can = self::current_user_can( ...$capabilities )();

			restore_current_blog();

			return $current_user_can;
		};
	}
}
