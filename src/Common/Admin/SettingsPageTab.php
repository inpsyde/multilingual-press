<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Settings page tab.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
class SettingsPageTab implements SettingsPageTabDataAccess {

	/**
	 * @var SettingsPageTabDataAccess
	 */
	private $data;

	/**
	 * @var SettingsPageView
	 */
	private $view;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @sine 3.0.0
	 *
	 * @param SettingsPageTabDataAccess $data Settings page data object.
	 * @param SettingsPageView          $view Settings page view object.
	 */
	public function __construct( SettingsPageTabDataAccess $data, SettingsPageView $view ) {

		$this->data = $data;

		$this->view = $view;
	}

	/**
	 * Returns the capability.
	 *
	 * @since 3.0.0
	 *
	 * @return string The capability.
	 */
	public function capability(): string {

		return $this->data->capability();
	}

	/**
	 * Returns the data object.
	 *
	 * @since 3.0.0
	 *
	 * @return SettingsPageTabDataAccess The data object.
	 */
	public function data(): SettingsPageTabDataAccess {

		return $this->data;
	}

	/**
	 * Returns the ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID.
	 */
	public function id(): string {

		return $this->data->id();
	}

	/**
	 * Returns the slug.
	 *
	 * @since 3.0.0
	 *
	 * @return string The slug.
	 */
	public function slug(): string {

		return $this->data->slug();
	}

	/**
	 * Returns the title.
	 *
	 * @since 3.0.0
	 *
	 * @return string The title.
	 */
	public function title(): string {

		return $this->data->title();
	}

	/**
	 * Returns the view object.
	 *
	 * @since 3.0.0
	 *
	 * @return SettingsPageView The view object.
	 */
	public function view(): SettingsPageView {

		return $this->view;
	}
}
