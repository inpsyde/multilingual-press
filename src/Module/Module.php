<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module;

/**
 * Module data type.
 *
 * @package Inpsyde\MultilingualPress\Module
 * @since   3.0.0
 */
class Module {

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var bool
	 */
	private $is_active;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id   Module ID.
	 * @param array  $data Optional. Module data. Defaults to empty array.
	 */
	public function __construct( string $id, array $data = [] ) {

		$this->id = $id;

		$this->description = (string) ( $data['description'] ?? '' );

		$this->is_active = (bool) ( $data['active'] ?? false );

		$this->name = (string) ( $data['name'] ?? '' );
	}

	/**
	 * Activates the module.
	 *
	 * @since 3.0.0
	 *
	 * @return Module Module instance.
	 */
	public function activate(): Module {

		$this->is_active = true;

		return $this;
	}

	/**
	 * Deactivates the module.
	 *
	 * @since 3.0.0
	 *
	 * @return Module Module instance.
	 */
	public function deactivate(): Module {

		$this->is_active = false;

		return $this;
	}

	/**
	 * Returns the description of the module.
	 *
	 * @since 3.0.0
	 *
	 * @return string The description of the module.
	 */
	public function description(): string {

		return $this->description;
	}

	/**
	 * Returns the ID of the module.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID of the module.
	 */
	public function id(): string {

		return $this->id;
	}

	/**
	 * Checks if the module is active.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the module is active.
	 */
	public function is_active(): bool {

		return $this->is_active;
	}

	/**
	 * Returns the name of the module.
	 *
	 * @since 3.0.0
	 *
	 * @return string The name of the module.
	 */
	public function name(): string {

		return $this->name;
	}
}
