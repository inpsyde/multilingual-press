<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Item;

/**
 * @package Inpsyde\MultilingualPress\Cache\Item
 * @since   3.0.0
 */
final class Value {

	/**
	 * @var bool
	 */
	private $hit;

	/**
	 * @var mixed|null
	 */
	private $value;

	/**
	 * Constructor.
	 *
	 * @param null $value Cached namespace.
	 * @param bool $hit   True if cache value is an hit.
	 */
	public function __construct( $value = null, bool $hit = false ) {

		$this->value = $value;

		$this->hit = $hit;
	}

	/**
	 * @return bool
	 */
	public function is_hit(): bool {

		return $this->hit;
	}

	/**
	 * @return mixed|null
	 */
	public function value() {

		return $this->value;
	}

}
