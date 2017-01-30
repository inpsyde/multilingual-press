<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Tab data structure.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
class EditSiteTabData {

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
	 * @param string $id         Tab ID.
	 * @param string $title      Title on used in the tab.
	 * @param string $slug       Page slug used in the URL.
	 * @param string $capability Optional. Capability required to view the tab. Defaults to "manage_sites".
	 */
	public function __construct(
		$id,
		$title,
		$slug,
		$capability = 'manage_sites'
	) {

		$this->id = (string) $id;

		$this->title = (string) $title;

		$this->slug = (string) $slug;

		$this->capability = (string) $capability;
	}

	/**
	 * Returns the capability.
	 *
	 * @since 3.0.0
	 *
	 * @return string The capability.
	 */
	public function capability() {

		return $this->capability;
	}

	/**
	 * Returns the ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID.
	 */
	public function id() {

		return $this->id;
	}

	/**
	 * Returns the slug.
	 *
	 * @since 3.0.0
	 *
	 * @return string The slug.
	 */
	public function slug() {

		return $this->slug;
	}

	/**
	 * Returns the title.
	 *
	 * @since 3.0.0
	 *
	 * @return string The title.
	 */
	public function title() {

		return $this->title;
	}
}
