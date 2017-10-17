<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Route;

/**
 * Interface for all route collection implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Route
 * @since 3 3.0.0
 */
interface Collection extends \IteratorAggregate {

	/**
	 * Adds the given route object to the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param Route $route Route object.
	 *
	 * @return Collection Collection object.
	 */
	public function add( Route $route ): Collection;

	/**
	 * Deletes the route object at the given index from the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param int $index Index of the route object.
	 *
	 * @return Collection Collection object.
	 */
	public function delete( int $index ): Collection;
}
