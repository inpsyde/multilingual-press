<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache;

interface TaggableCacheItem extends CacheItem {


	/**
	 * Return current item tags.
	 *
	 * @return string[]
	 */
	public function tags(): array;

	/**
	 * Check if current cache item have one (or more) tags.
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function has_tag( string ...$tags ): bool;

	/**
	 * Add one or more tags ot cache item.
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function add_tags( string ...$tags ): bool;

	/**
	 * Add one or more tags ot cache item.
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function remove_tags( string ...$tags ): bool;


	/**
	 * Overwrite tags withe given tag(s).
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function use_tags( string ...$tags ): bool;
}