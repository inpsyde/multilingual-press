<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\ContextAwareFilter;
use Inpsyde\MultilingualPress\Common\Filter;

/**
 * Redirect filter respecting the user setting.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class RedirectFilter implements Filter {

	use ContextAwareFilter;

	/**
	 * @var SettingsRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SettingsRepository $repository Settings repository object.
	 */
	public function __construct( SettingsRepository $repository ) {

		$this->repository = $repository;

		$this->callback = [ $this, 'filter_redirect' ];

		// TODO: Use class constant of Redirector class.
		$this->hook = 'mlp_do_redirect';
	}

	/**
	 * Filters the redirect according to the current user's setting.
	 *
	 * @since   3.0.0
	 * @todo    Adapt hook as soon as it is a class constant.
	 * @wp-hook mlp_do_redirect
	 *
	 * @param bool $redirect Current redirect status.
	 *
	 * @return bool The (filtered) redirect status.
	 */
	public function filter_redirect( $redirect ) {

		return ! $this->repository->get_user_setting() && $redirect;
	}
}
