<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Settings page tab data structure.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
class SettingsPageTabData {

	/**
	 * @var string
	 */
	private $capability;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @sine 3.0.0
	 *
	 * @param string $capability Capability required to view the tab.
	 * @param string $id         Tab ID.
	 * @param string $title      Title on used in the tab.
	 * @param string $slug       Slug used in the URL.
	 */
	public function __construct(
		string $capability,
		string $id,
		string $title,
		string $slug
	) {

		$this->capability = $capability;

		$this->id = $id;

		$this->title = $title;

		$this->slug = $slug;
	}

	/**
	 * Returns the capability.
	 *
	 * @since 3.0.0
	 *
	 * @return string The capability.
	 */
	public function capability(): string {

		return $this->capability;
	}

	/**
	 * Returns the ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID.
	 */
	public function id(): string {

		return $this->id;
	}

	/**
	 * Returns the slug.
	 *
	 * @since 3.0.0
	 *
	 * @return string The slug.
	 */
	public function slug(): string {

		return $this->slug;
	}

	/**
	 * Returns the title.
	 *
	 * @since 3.0.0
	 *
	 * @return string The title.
	 */
	public function title(): string {

		return $this->title;
	}
}
