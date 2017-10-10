<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Exception;

/**
 * @package MultilingualPress\Cache\Exception
 * @since   3.0.0
 */
class BadCacheItemRegistration extends Exception {

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct( 'Cache item logic registration is not possible during cache update requests.' );
	}

}
