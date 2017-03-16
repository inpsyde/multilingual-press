<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

/**
 * Object-cache-based noredirect storage implementation.
 *
 * Obviously, this should only be used for logged-in users, so they do not mutually affect each other.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class NoredirectObjectCacheStorage implements NoredirectStorage {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * Adds the given language to the storage.
	 *
	 * @since 3.0.0
	 *
	 * @param string $language Language code.
	 *
	 * @return bool Whether or not the language was stored right now (i.e., returns false if it was already in storage).
	 */
	public function add_language( string $language ): bool {

		$languages = $this->get_languages();

		if ( $languages && $this->has_language( $language ) ) {
			return false;
		}

		$languages[] = (string) $language;

		wp_cache_set( $this->key(), $languages, '', NoredirectStorage::LIFETIME_IN_SECONDS );

		return true;
	}

	/**
	 * Checks if the given language has been stored before.
	 *
	 * @since 3.0.0
	 *
	 * @param string $language Language code.
	 *
	 * @return bool Whether or not the given language has been stored before.
	 */
	public function has_language( string $language ): bool {

		$languages = $this->get_languages();
		if ( ! $languages ) {
			return false;
		}

		return in_array( $language, $languages, true );
	}

	/**
	 * Returns the currently stored languages.
	 *
	 * @return string[] Languages.
	 */
	private function get_languages(): array {

		$languages = wp_cache_get( $this->key() );
		if ( ! $languages || ! is_array( $languages ) ) {
			return [];
		}

		return array_map( 'strval', $languages );
	}

	/**
	 * Returns the cache key for the current user.
	 *
	 * @return string Cache key.
	 */
	private function key(): string {

		if ( ! $this->key ) {
			$this->key = 'multilingualpress.' . NoredirectStorage::KEY . '.' . get_current_user_id();
		}

		return $this->key;
	}
}
