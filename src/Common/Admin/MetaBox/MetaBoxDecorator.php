<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Trait to be used to decorate a meta box implementation by proxying to an existing priority-aware meta box.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 *
 * @see MetaBox
 */
trait MetaBoxDecorator {

	/**
	 * @var PriorityAwareMetaBox
	 */
	private $meta_box;

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

		return $this->meta_box->allow_for_screens_by_base( ...$screens );
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

		return $this->meta_box->allow_for_screens_by_id( ...$screens );
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

		return $this->meta_box->allow_for_screens_by_parent( ...$screens );
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

		return $this->meta_box->with_context( $context );
	}

	/**
	 * Returns the meta box context.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box context.
	 */
	public function context(): string {

		return $this->meta_box->context();
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

		return $this->meta_box->with_id( $id );
	}

	/**
	 * Returns the meta box ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box ID.
	 */
	public function id(): string {

		return $this->meta_box->id();
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

		return $this->meta_box->is_allowed_for_screen( $screen );
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

		return $this->meta_box->with_title( $title );
	}

	/**
	 * Returns the meta box title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box title.
	 */
	public function title(): string {

		return $this->meta_box->title();
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

		return $this->meta_box->with_priority( $priority );
	}

	/**
	 * Returns the meta box priority.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box priority.
	 */
	public function priority(): string {

		return $this->meta_box->priority();
	}

	/**
	 * Decorates the meta box by proxying to the given priority-aware meta box.
	 *
	 * @param PriorityAwareMetaBox $meta_box Meta box object.
	 *
	 * @return void
	 */
	private function decorate_meta_box( PriorityAwareMetaBox $meta_box ) {

		$this->meta_box = $meta_box;
	}
}
