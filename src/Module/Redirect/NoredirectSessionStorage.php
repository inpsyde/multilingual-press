<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

/**
 * Session-based noredirect storage implementation.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class NoredirectSessionStorage implements NoredirectStorage {

	/**
	 * Adds the given language to the storage.
	 *
	 * @since 3.0.0
	 *
	 * @param string $language Language code.
	 *
	 * @return bool Whether or not the language was stored right now (i.e., returns false if it was already in storage).
	 */
	public function add_language( $language ) {

		$this->ensure_session();

		if ( empty( $_SESSION[ NoredirectStorage::KEY ] ) ) {
			$_SESSION[ NoredirectStorage::KEY ] = [];
		} else {
			$_SESSION[ NoredirectStorage::KEY ] = (array) $_SESSION[ NoredirectStorage::KEY ];

			if ( $this->has_language( $language ) ) {
				return false;
			}
		}

		$_SESSION[ NoredirectStorage::KEY ][] = (string) $language;

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
	public function has_language( $language ) {

		$this->ensure_session();

		if ( empty( $_SESSION[ NoredirectStorage::KEY ] ) ) {
			return false;
		}

		return in_array( $language, (array) $_SESSION[ NoredirectStorage::KEY ], true );
	}

	/**
	 * Ensures a session.
	 *
	 * @return void
	 */
	private function ensure_session() {

		if ( ! isset( $_SESSION ) && ! session_id() ) {
			session_start();
		}
	}
}
