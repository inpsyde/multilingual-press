<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\UserAdminLanguage;

/**
 * User admin language locale filter.
 *
 * @package Inpsyde\MultilingualPress\Module\UserAdminLanguage
 * @since   3.0.0
 */
class LocaleFilter {

	/**
	 * @var callable
	 */
	private $filter;

	/**
	 * @var string
	 */
	private $hook = 'locale';

	/**
	 * @var LanguageRepository
	 */
	private $language_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param LanguageRepository $language_repository Language repository object.
	 */
	public function __construct( LanguageRepository $language_repository ) {

		$this->language_repository = $language_repository;

		$this->filter = [ $this, 'filter_locale' ];
	}

	/**
	 * Removes the filter.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the filter was removed successfully.
	 */
	public function disable() {

		if ( has_filter( $this->hook, $this->filter ) ) {
			remove_filter( $this->hook, $this->filter );

			return true;
		};

		return false;
	}

	/**
	 * Adds the filter.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the filter was added successfully.
	 */
	public function enable() {

		if ( has_filter( $this->hook, $this->filter ) ) {
			return false;
		};

		add_filter( $this->hook, $this->filter );

		return true;
	}

	/**
	 * Filters the locale and returns the user admin language of the current user, if set.
	 *
	 * @since   3.0.0
	 * @wp-hook locale
	 *
	 * @param string $locale The current locale.
	 *
	 * @return string The (filtered) locale.
	 */
	public function filter_locale( $locale ) {

		$user_language = $this->language_repository->get_user_language();

		return $user_language ?: (string) $locale;
	}
}
