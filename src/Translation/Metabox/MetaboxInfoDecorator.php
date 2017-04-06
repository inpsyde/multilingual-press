<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Metabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
trait MetaboxInfoDecorator {

	/**
	 * @var GenericMetaboxInfo
	 */
	private $info;

	/**
	 * @param MetaboxInfo $info
	 */
	private function decorate_metabox_info( MetaboxInfo $info ) {
		$this->info = $info;
	}

	/**
	 * @return string
	 */
	public function id(): string {

		return $this->info->id();
	}

	/**
	 * @param string $id
	 *
	 * @return MetaboxInfo
	 */
	public function with_id( string $id ): MetaboxInfo {

		return $this->info->with_id( $id );
	}

	/**
	 * @return string
	 */
	public function title(): string {

		return $this->info->title();
	}

	/**
	 * @param string $title
	 *
	 * @return MetaboxInfo
	 */
	public function with_title( string $title ): MetaboxInfo {

		return $this->info->with_title( $title );
	}

	/**
	 * @return string
	 */
	public function context(): string {

		return $this->info->context();
	}

	/**
	 * @param string $context
	 *
	 * @return MetaboxInfo
	 */
	public function with_context( string $context ): MetaboxInfo {

		return $this->info->with_context( $context );
	}

	/**
	 * @return string
	 */
	public function priority(): string {

		return $this->info->priority();

	}

	/**
	 * @param string $priority
	 *
	 * @return PriorityAwareMetaboxInfo
	 */
	public function with_priority( string $priority ): PriorityAwareMetaboxInfo {

		return $this->info->with_priority( $priority );
	}

	/**
	 * @param \WP_Screen $screen
	 *
	 * @return bool
	 */
	public function is_allowed_for_screen( \WP_Screen $screen ): bool {

		return $this->info->is_allowed_for_screen( $screen );
	}

	/**
	 * @param array $screen_id
	 *
	 * @return MetaboxInfo
	 */
	public function allow_screen( ...$screen_id ): MetaboxInfo {

		return $this->info->allow_screen( ...$screen_id );
	}

	/**
	 * @param array $screen_base
	 *
	 * @return MetaboxInfo
	 */
	public function allow_screen_base( ...$screen_base ): MetaboxInfo {

		return $this->info->allow_screen_base( ...$screen_base );
	}

	/**
	 * @param array $screen_parent
	 *
	 * @return MetaboxInfo
	 */
	public function allow_screen_parent( ...$screen_parent ): MetaboxInfo {

		return $this->info->allow_screen_parent( ...$screen_parent );
	}
}