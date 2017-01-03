<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\UserAdminLanguage;

use Inpsyde\MultilingualPress\Common\ContextAwareFilter;
use Inpsyde\MultilingualPress\Common\Filter;

/**
 * User admin language locale filter.
 *
 * @package Inpsyde\MultilingualPress\Module\UserAdminLanguage
 * @since   3.0.0
 */
final class LocaleFilter implements Filter {

	use ContextAwareFilter;

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

		$this->callback = [ $this, 'filter_locale' ];

		$this->hook = 'locale';
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
