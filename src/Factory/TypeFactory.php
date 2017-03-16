<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Factory;

use Inpsyde\MultilingualPress\Common\Factory;
use Inpsyde\MultilingualPress\Common\Type\AliasAwareLanguage;
use Inpsyde\MultilingualPress\Common\Type\EscapedURL;
use Inpsyde\MultilingualPress\Common\Type\FilterableTranslation;
use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Common\Type\SemanticVersionNumber;
use Inpsyde\MultilingualPress\Common\Type\Translation;
use Inpsyde\MultilingualPress\Common\Type\URL;
use Inpsyde\MultilingualPress\Common\Type\VersionNumber;

/**
 * Factory for all common data type objects.
 *
 * @package Inpsyde\MultilingualPress\Factory
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
	public function create_language( array $args = [], string $class = '' ): Language {

		$default_class = AliasAwareLanguage::class;

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->get_factory( Language::class, $default_class )->create( $args, $class ?: $default_class );
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
	public function create_translation( array $args = [], string $class = '' ): Translation {

		$default_class = FilterableTranslation::class;

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->get_factory( Translation::class, $default_class )->create( $args, $class ?: $default_class );
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
	public function create_url( array $args = [], string $class = '' ): URL {

		$default_class = EscapedURL::class;

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->get_factory( URL::class, $default_class )->create( $args, $class ?: $default_class );
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
	public function create_version_number( array $args = [], string $class = '' ): VersionNumber {

		$default_class = SemanticVersionNumber::class;

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->get_factory( VersionNumber::class, $default_class )->create( $args, $class ?: $default_class );
	}

	/**
	 * Returns the factory instance for the given base.
	 *
	 * If the according factory doesn't exist yet, it will be created first, with the given class as default class.
	 *
	 * @param string $base          Fully qualified name of the base class or interface.
	 * @param string $default_class Fully qualified class name.
	 *
	 * @return Factory Factory instance.
	 */
	private function get_factory( string $base, string $default_class ) {

		if ( empty( $this->factories[ $base ] ) ) {
			$this->factories[ $base ] = GenericFactory::with_default_class( $base, $default_class );
		}

		return $this->factories[ $base ];
	}
}
