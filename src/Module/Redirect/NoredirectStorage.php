<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

/**
 * Interface for all noredirect storage implementations.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
interface NoredirectStorage {

	/**
	 * Hook name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_LIFETIME = 'multilingualpress.noredirect_storage_lifetime';

	/**
	 * Lifetime for all stored languages.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const LIFETIME_IN_SECONDS = 5 * MINUTE_IN_SECONDS;

	/**
	 * Noredirect key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY = 'noredirect';

	/**
	 * Adds the given language to the storage.
	 *
	 * @since 3.0.0
	 *
	 * @param string $language Language code.
	 *
	 * @return bool Whether or not the language was stored right now (i.e., returns false if it was already in storage).
	 */
	public function add_language( string $language ): bool;

	/**
	 * Checks if the given language has been stored before.
	 *
	 * @since 3.0.0
	 *
	 * @param string $language Language code.
	 *
	 * @return bool Whether or not the given language has been stored before.
	 */
	public function has_language( string $language ): bool;
}
