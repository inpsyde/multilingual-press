<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Integration;

use Inpsyde\MultilingualPress\Installation\SystemChecker;

use const Inpsyde\MultilingualPress\ACTION_ACTIVATION;

/**
 * WP-CLI integration controller.
 *
 * @package Inpsyde\MultilingualPress\Integration
 * @since   3.0.0
 */
final class WPCLI implements Integration {

	/**
	 * Integrates some (possibly external) service with MultilingualPress.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the service was integrated successfully.
	 */
	public function integrate(): bool {

		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return false;
		}

		if ( did_action( ACTION_ACTIVATION ) ) {
			// Force installation check and thus allow to execute installation or upgrade routines.
			add_filter( SystemChecker::ACTION_FORCE_CHECK, '__return_true' );
		}

		return true;
	}
}
