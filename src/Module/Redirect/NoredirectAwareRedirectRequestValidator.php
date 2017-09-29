<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\RequestValidator;

use function Inpsyde\MultilingualPress\get_current_site_language;

/**
 * Noredirect-aware request validator implementation to be used for (potential) redirect requests.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class NoredirectAwareRedirectRequestValidator implements RequestValidator {

	/**
	 * Filter hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER = 'multilingualpress.do_redirect';

	/**
	 * @var NoredirectStorage
	 */
	private $noredirect_storage;

	/**
	 * @var SettingsRepository
	 */
	private $settings_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SettingsRepository $settings_repository Settings repository object.
	 * @param NoredirectStorage  $noredirect_storage  Noredirect session storage object.
	 */
	public function __construct( SettingsRepository $settings_repository, NoredirectStorage $noredirect_storage ) {

		$this->settings_repository = $settings_repository;

		$this->noredirect_storage = $noredirect_storage;
	}

	/**
	 * Checks if the request is valid.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $context Optional. Validation context. Defaults to null.
	 *
	 * @return bool Whether or not the request is valid.
	 */
	public function is_valid( $context = null ): bool {

		if ( ! $this->settings_repository->get_site_setting() ) {
			return false;
		}

		if ( $this->settings_repository->get_user_setting() ) {
			return false;
		}

		if ( $this->noredirect_storage->has_language( get_current_site_language() ) ) {
			return false;
		}

		/**
		 * Filters if the current request should be redirected.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $redirect Whether or not the current request should be redirected.
		 */
		return (bool) apply_filters( static::FILTER, true );
	}
}
