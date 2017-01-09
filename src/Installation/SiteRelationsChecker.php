<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

/**
 * Interface for all site relations checker implementations.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
interface SiteRelationsChecker {

	/**
	 * Checks if there are at least two sites related to each other, and renders an admin notice if not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not there are at least two sites related to each other.
	 */
	public function check_relations();
}
