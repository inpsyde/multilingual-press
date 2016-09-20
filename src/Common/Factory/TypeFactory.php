<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Factory;

use Inpsyde\MultilingualPress\Common\Factory;
use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Common\Type\Translation;
use Inpsyde\MultilingualPress\Common\Type\URL;
use Inpsyde\MultilingualPress\Common\Type\VersionNumber;

/**
 * Factory for all common data type objects.
 *
 * @package Inpsyde\MultilingualPress\Common\Factory
 * @since   3.0.0
 */
class TypeFactory {

	/**
	 * @var Factory[]
	 */
	private $factories = [];

	/**
	 * Returns a new language object of the given (or default) class, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to escaped URL implementation.
	 *
	 * @return Language Language object of the given (or default) class, instantiated with the given arguments.
	 */
	public function create_language( array $args = [], $class = '' ) {

		return $this->get_factory(
			'\Inpsyde\MultilingualPress\Common\Type\Language',
			$class ?: '\Inpsyde\MultilingualPress\Common\Type\AliasAwareLanguage'
		)->create( $args, $class );
	}

	/**
	 * Returns a new translation object of the given (or default) class, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to escaped URL implementation.
	 *
	 * @return Translation Translation object of the given (or default) class, instantiated with the given arguments.
	 */
	public function create_translation( array $args = [], $class = '' ) {

		return $this->get_factory(
			'\Inpsyde\MultilingualPress\Common\Type\Translation',
			$class ?: '\Inpsyde\MultilingualPress\Common\Type\FilterableTranslation'
		)->create( $args, $class );
	}

	/**
	 * Returns a new URL object of the given (or default) class, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to escaped URL implementation.
	 *
	 * @return URL URL object of the given (or default) class, instantiated with the given arguments.
	 */
	public function create_url( array $args = [], $class = '' ) {

		return $this->get_factory(
			'\Inpsyde\MultilingualPress\Common\Type\URL',
			$class ?: '\Inpsyde\MultilingualPress\Common\Type\EscapedURL'
		)->create( $args, $class );
	}

	/**
	 * Returns a new version number object of the given (or default) class, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to semantic version number implementation.
	 *
	 * @return VersionNumber Version number object of the given (or default) class, instantiated with the given
	 *                       arguments.
	 */
	public function create_version_number( array $args = [], $class = '' ) {

		return $this->get_factory(
			'\Inpsyde\MultilingualPress\Common\Type\VersionNumber',
			$class ?: '\Inpsyde\MultilingualPress\Common\Type\SemanticVersionNumber'
		)->create( $args, $class );
	}

	/**
	 * Returns the factory instance for the given base.
	 *
	 * If the according factory doesn't exist yet, it will be created first, with the given class as default class.
	 *
	 * @param string $base  Fully qualified name of the base class or interface.
	 * @param string $class Fully qualified class name.
	 *
	 * @return Factory Factory instance.
	 */
	private function get_factory( $base, $class ) {

		if ( empty( $this->factories[ $base ] ) ) {
			$this->factories[ $base ] = new Factory( $base, $class );
		}

		return $this->factories[ $base ];
	}
}
