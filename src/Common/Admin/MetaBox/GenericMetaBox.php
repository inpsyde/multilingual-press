<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Generic (priority-aware) meta box implementation.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
final class GenericMetaBox implements PriorityAwareMetaBox {

	/**
	 * @var string
	 */
	private $context;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $priority;

	/**
	 * @var string[][]
	 */
	private $screens = [
		self::KEY_SCREEN_ID     => [],
		self::KEY_SCREEN_BASE   => [],
		self::KEY_SCREEN_PARENT => [],
	];

	/**
	 * @var string
	 */
	private $title;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id       Optional. Meta box ID. Defaults to empty string.
	 * @param string $title    Optional. Meta box title. Defaults to empty string.
	 * @param array  $screens  Optional. One or more screen objects, IDs, bases or parents. Defaults to empty array.
	 * @param string $context  Optional. Meta box context. Defaults to 'advanced'.
	 * @param string $priority Optional. Meta box priority. Defaults to 'default'.
	 */
	public function __construct(
		string $id = '',
		string $title = '',
		array $screens = [],
		string $context = 'advanced',
		string $priority = 'default'
	) {

		$this->id = $id;

		$this->title = $title;

		if ( $screens ) {
			array_walk( $screens, [ $this, 'allow_for_screen' ] );
		}

		$this->context = $context;

		$this->priority = $priority;
	}

	/**
	 * Allows the meta box for all given screens, based on their base.
	 *
	 * @since 3.0.0
	 *
	 * @param array ...$screens One or more screen objects, arrays or bases.
	 *
	 * @return MetaBox
	 */
	public function allow_for_screens_by_base( ...$screens ): MetaBox {

		return $this->allow_for_screens_by_key( $screens, self::KEY_SCREEN_BASE );
	}

	/**
	 * Allows the meta box for all given screens, based on their ID.
	 *
	 * @since 3.0.0
	 *
	 * @param array ...$screens One or more screen objects, arrays or IDs.
	 *
	 * @return MetaBox
	 */
	public function allow_for_screens_by_id( ...$screens ): MetaBox {

		return $this->allow_for_screens_by_key( $screens, self::KEY_SCREEN_ID );
	}

	/**
	 * Allows the meta box for all given screens, based on their parent base.
	 *
	 * @since 3.0.0
	 *
	 * @param array ...$screens One or more screen objects, arrays or parent bases.
	 *
	 * @return MetaBox
	 */
	public function allow_for_screens_by_parent( ...$screens ): MetaBox {

		return $this->allow_for_screens_by_key( $screens, self::KEY_SCREEN_PARENT );
	}

	/**
	 * Returns an instance with the given meta box context.
	 *
	 * @since 3.0.0
	 *
	 * @param string $context Meta box context to set.
	 *
	 * @return MetaBox
	 */
	public function with_context( string $context ): MetaBox {

		$clone = clone $this;

		$clone->context = $context;

		return $clone;
	}

	/**
	 * Returns the meta box context.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box context.
	 */
	public function context(): string {

		return $this->context;
	}

	/**
	 * Returns an instance with the given meta box ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Meta box ID to set.
	 *
	 * @return MetaBox
	 */
	public function with_id( string $id ): MetaBox {

		$clone = clone $this;

		$clone->id = $id;

		return $clone;
	}

	/**
	 * Returns the meta box ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box ID.
	 */
	public function id(): string {

		return $this->id;
	}

	/**
	 * Checks if the meta box is allowed for the given screen.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Screen $screen Screen object to check against.
	 *
	 * @return bool Whether or not the meta box is allowed for the given screen.
	 */
	public function is_allowed_for_screen( \WP_Screen $screen ): bool {

		return
			in_array( (string) $screen->id, $this->screens[ self::KEY_SCREEN_ID ] ?? [], true )
			|| in_array( (string) $screen->base, $this->screens[ self::KEY_SCREEN_BASE ] ?? [], true )
			|| in_array( (string) $screen->parent_base, $this->screens[ self::KEY_SCREEN_PARENT ] ?? [], true );
	}

	/**
	 * Returns an instance with the given meta box title.
	 *
	 * @since 3.0.0
	 *
	 * @param string $title Meta box title to set.
	 *
	 * @return MetaBox
	 */
	public function with_title( string $title ): MetaBox {

		$clone = clone $this;

		$clone->title = $title;

		return $clone;
	}

	/**
	 * Returns the meta box title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box title.
	 */
	public function title(): string {

		return $this->title;
	}

	/**
	 * Returns an instance with the given meta box priority.
	 *
	 * @since 3.0.0
	 *
	 * @param string $priority Meta box priority to set.
	 *
	 * @return PriorityAwareMetaBox
	 */
	public function with_priority( string $priority ): PriorityAwareMetaBox {

		$clone = clone $this;

		$clone->priority = $priority;

		return $clone;
	}

	/**
	 * Returns the meta box priority.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box priority.
	 */
	public function priority(): string {

		return $this->priority;
	}

	/**
	 * Allows the meta box for the given screen, based on all available properties, such as ID, base and parent base.
	 *
	 * @param \WP_Screen|array|string $screen Screen object, array, ID, base or parent base.
	 *
	 * @return void
	 */
	private function allow_for_screen( $screen ) {

		$screen_data = $this->normalize_screen_data( $screen, true );

		if ( ! empty( $screen_data[ self::KEY_SCREEN_ID ] ) ) {
			$this->allow_for_screens_by_id( $screen_data[ self::KEY_SCREEN_ID ] );
		}

		if ( ! empty( $screen_data[ self::KEY_SCREEN_BASE ] ) ) {
			$this->allow_for_screens_by_base( $screen_data[ self::KEY_SCREEN_BASE ] );
		}

		if ( ! empty( $screen_data[ self::KEY_SCREEN_PARENT ] ) ) {
			$this->allow_for_screens_by_parent( $screen_data[ self::KEY_SCREEN_PARENT ] );
		}
	}

	/**
	 * Allows the meta box for all given screens, based on the given property key.
	 *
	 * @param array  $screens One or more screen objects, arrays, IDs, bases or parent bases.
	 * @param string $key     Screen property key.
	 *
	 * @return MetaBox
	 */
	private function allow_for_screens_by_key( array $screens, string $key ): MetaBox {

		$screens = array_map( function ( $screen ) use ( $key ) {

			return $this->normalize_screen_data( $screen, false, $key );
		}, $screens );

		$this->screens[ $key ] = array_merge(
			$this->screens[ $key ],
			array_diff( array_column( $screens, $key ), $this->screens[ $key ] )
		);

		return $this;
	}

	/**
	 * Normalizes the given screen object, array, ID, base or parent base.
	 *
	 * @param \WP_Screen|array|string $screen  Screen object, array, ID, base or parent base.
	 * @param bool                    $id_only Optional. Return the screen ID only? Defaults to false.
	 * @param string                  $key     Optional. Screen property key. Defaults to ID.
	 *
	 * @return string[] An associative array with screen ID, base and parent base, if available.
	 */
	private function normalize_screen_data( $screen, bool $id_only = false, string $key = self::KEY_SCREEN_ID ): array {

		if ( $screen instanceof \WP_Screen ) {
			return [
				self::KEY_SCREEN_ID     => (string) $screen->id,
				self::KEY_SCREEN_BASE   => $id_only ? '' : (string) $screen->base,
				self::KEY_SCREEN_PARENT => $id_only ? '' : (string) $screen->parent_base,
			];
		}

		$default_data = [
			self::KEY_SCREEN_ID     => '',
			self::KEY_SCREEN_BASE   => '',
			self::KEY_SCREEN_PARENT => '',
		];

		if ( is_string( $screen ) ) {
			return shortcode_atts( $default_data, [
				$key => $screen,
			] );
		}

		if ( is_array( $screen ) ) {
			return shortcode_atts(
				$default_data,
				array_filter( array_change_key_case( $screen, CASE_LOWER ), 'is_string' )
			);
		}

		return $default_data;
	}
}
