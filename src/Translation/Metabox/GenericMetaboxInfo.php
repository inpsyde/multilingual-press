<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Metabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class GenericMetaboxInfo implements PriorityAwareMetaboxInfo {

	/**
	 * @var array
	 */
	public $screens = [
		self::SCREEN_ID     => [],
		self::SCREEN_BASE   => [],
		self::SCREEN_PARENT => [],
	];

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $context;

	/**
	 * @var string
	 */
	private $priority;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param string $id
	 * @param string $title
	 * @param array  $screens
	 * @param string $context
	 * @param string $priority
	 */
	public function __construct(
		string $id = '',
		string $title = '',
		array $screens = [],
		string $context = 'advanced',
		string $priority = 'default'
	) {

		$this->id       = $id;
		$this->title    = $title;
		$this->context  = $context;
		$this->priority = $priority;

		array_walk( $screens, [ $this, 'initialize_screen_data' ] );
	}

	/**
	 * @return string
	 */
	public function id(): string {

		return $this->id;
	}

	/**
	 * @param string $id
	 *
	 * @return MetaboxInfo
	 */
	public function with_id( string $id ): MetaboxInfo {

		$instance = clone $this;

		$instance->id = $id;

		return $instance;
	}

	/**
	 * @return string
	 */
	public function title(): string {

		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return MetaboxInfo
	 */
	public function with_title( string $title ): MetaboxInfo {

		$instance = clone $this;

		$instance->title = $title;

		return $instance;
	}

	/**
	 * @return string
	 */
	public function context(): string {

		return $this->context;
	}

	/**
	 * @param string $context
	 *
	 * @return MetaboxInfo
	 */
	public function with_context( string $context ): MetaboxInfo {

		$instance = clone $this;

		$instance->context = $context;

		return $instance;
	}

	/**
	 * @return string
	 */
	public function priority(): string {

		return $this->priority;

	}

	/**
	 * @param string $priority
	 *
	 * @return PriorityAwareMetaboxInfo
	 */
	public function with_priority( string $priority ): PriorityAwareMetaboxInfo {

		$instance = clone $this;

		$instance->priority = $priority;

		return $instance;
	}

	/**
	 * @param \WP_Screen $screen
	 *
	 * @return bool
	 */
	public function is_allowed_for_screen( \WP_Screen $screen ): bool {

		return
			in_array( $screen->id, $this->screens[ self::SCREEN_ID ] ?? [] )
			|| in_array( $screen->base, $this->screens[ self::SCREEN_BASE ] ?? [] )
			|| in_array( $screen->parent_base, $this->screens[ self::SCREEN_PARENT ] ?? [] );
	}

	/**
	 * @param array $screen_id
	 *
	 * @return MetaboxInfo
	 */
	public function allow_screen( ...$screen_id ): MetaboxInfo {

		return $this->allow_screen_data( $screen_id, self::SCREEN_ID );
	}

	/**
	 * @param array $screen_base
	 *
	 * @return MetaboxInfo
	 */
	public function allow_screen_base( ...$screen_base ): MetaboxInfo {

		return $this->allow_screen_data( $screen_base, self::SCREEN_BASE );
	}

	/**
	 * @param array $screen_parent
	 *
	 * @return MetaboxInfo
	 */
	public function allow_screen_parent( ...$screen_parent ): MetaboxInfo {

		return $this->allow_screen_data( $screen_parent, self::SCREEN_PARENT );
	}

	/**
	 * @param array  $screens_data
	 * @param string $key
	 *
	 * @return MetaboxInfo
	 */
	private function allow_screen_data( array $screens_data, string $key ): MetaboxInfo {

		$normalizer = function ( $screen_data ) use ( $key ) {

			return $this->normalize_screen_data( $screen_data, true, $key );
		};

		/** @var string[] $screens_data */
		$screens_data = array_column( array_map( $normalizer, $screens_data ), $key );

		$this->screens[ $key ] = array_merge(
			$this->screens[ $key ],
			array_diff( $screens_data, $this->screens[ $key ] )
		);

		return $this;
	}

	/**
	 * @param array $screens_data
	 *
	 * @return void
	 */
	private function initialize_screen_data( $screens_data ) {

		if ( ! $screens_data ) {
			return;
		}

		array_walk( $screens_data, function ( $screen_data ) {

			$screen_data = $this->normalize_screen_data( $screen_data, false );

			if ( ! empty( $screen_data[ self::SCREEN_ID ] ) ) {
				$this->allow_screen( $screen_data[ self::SCREEN_ID ] );
			}

			if ( ! empty( $screen_data[ self::SCREEN_BASE ] ) ) {
				$this->allow_screen_base( $screen_data[ self::SCREEN_BASE ] );
			}

			if ( ! empty( $screen_data[ self::SCREEN_PARENT ] ) ) {
				$this->allow_screen_parent( $screen_data[ self::SCREEN_PARENT ] );
			}
		} );
	}

	/**
	 * @param \WP_Screen|array|string $screen_data
	 * @param bool                    $full_object
	 * @param string                  $string_key
	 *
	 * @return \string[]
	 */
	private function normalize_screen_data( $screen_data, $full_object = true, $string_key = self::SCREEN_ID ) {

		if ( $screen_data instanceof \WP_Screen ) {
			return [
				self::SCREEN_ID     => (string) $screen_data->id,
				self::SCREEN_BASE   => $full_object ? (string) $screen_data->base : '',
				self::SCREEN_PARENT => $full_object ? (string) $screen_data->parent_base : '',
			];
		}

		if ( is_string( $screen_data ) ) {
			return [
				self::SCREEN_ID     => $string_key === self::SCREEN_ID ? $screen_data : '',
				self::SCREEN_BASE   => $string_key === self::SCREEN_BASE ? $screen_data : '',
				self::SCREEN_PARENT => $string_key === self::SCREEN_PARENT ? $screen_data : '',
			];
		}

		if ( is_array( $screen_data ) ) {
			$screen_data = array_filter( array_change_key_case( $screen_data, CASE_LOWER ), 'is_string' );

			return [
				self::SCREEN_ID     => $screen_data[ self::SCREEN_ID ] ?? '',
				self::SCREEN_BASE   => $screen_data[ self::SCREEN_BASE ] ?? '',
				self::SCREEN_PARENT => $screen_data[ self::SCREEN_PARENT ] ?? '',
			];
		}

		return [
			self::SCREEN_ID     => '',
			self::SCREEN_BASE   => '',
			self::SCREEN_PARENT => '',
		];

	}

}