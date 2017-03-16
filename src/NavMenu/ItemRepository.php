<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\NavMenu;

/**
 * Interface for all item repository implementations.
 *
 * @package Inpsyde\MultilingualPress\NavMenu
 * @since   3.0.0
 */
interface ItemRepository {

	/**
	 * Meta key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const META_KEY_SITE_ID = '_blog_id';

	/**
	 * Returns the according items for the sites with the given IDs.
	 *
	 * @since 3.0.0
	 *
	 * @param int[] $site_ids Site IDs.
	 *
	 * @return object[] The items for the sites with the given IDs.
	 */
	public function get_items_for_sites( array $site_ids ): array;
}
