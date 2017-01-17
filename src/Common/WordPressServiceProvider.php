<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

/**
 * Service provider for all WordPress objects.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
final class WordPressServiceProvider implements ServiceProvider {

	/**
	 * Registers the provided services on the given container.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function register( Container $container ) {

		$container->share( 'multilingualpress.wpdb', function () {

			return $GLOBALS['wpdb'];
		} );
	}
}
