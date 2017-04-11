<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Meta box UI registry.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post
 * @since   3.0.0
 */
class MetaBoxUIRegistry {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_UI_SELECTED = 'multilingualpress.meta_box_ui';

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_SELECT_UI = 'multilingualpress.select_meta_box_ui';

	/**
	 * @var string[][]
	 */
	private $names = [];

	/**
	 * @var MetaBoxUI[][]
	 */
	private $objects = [];

	/**
	 * @var MetaBoxUI[]
	 */
	private $selected_ui = [];

	/**
	 * Returns an array with all meta box IDs.
	 *
	 * @since    3.0.0
	 *
	 * @param UIAwareMetaBoxRegistrar $registrar
	 *
	 * @return string[] An array with all meta box IDs.
	 */
	public function get_ids( UIAwareMetaBoxRegistrar $registrar ): array {

		$registrar_id = $registrar->identify_for_ui();

		return array_key_exists( $registrar_id, $this->names ) ? array_keys( $this->names[ $registrar_id ] ) : [];
	}

	/**
	 * Returns an array with all meta box names.
	 *
	 * @since 3.0.0
	 *
	 * @param UIAwareMetaBoxRegistrar $registrar
	 *
	 * @return string[] An array with all meta box IDs as keys and the names as values.
	 */
	public function get_names( UIAwareMetaBoxRegistrar $registrar ): array {

		$registrar_id = $registrar->identify_for_ui();

		return array_key_exists( $registrar_id, $this->names ) ? $this->names[ $registrar_id ] : [];
	}

	/**
	 * Returns an array with all meta box objects.
	 *
	 * @since 3.0.0
	 *
	 * @param UIAwareMetaBoxRegistrar $registrar
	 *
	 * @return MetaBoxUI[] An array with all meta box IDs as keys and the objects as values.
	 */
	public function get_objects( UIAwareMetaBoxRegistrar $registrar ): array {

		$registrar_id = $registrar->identify_for_ui();

		return array_key_exists( $registrar_id, $this->objects ) ? $this->objects[ $registrar_id ] : [];
	}

	/**
	 * Registers the given meta box UI.
	 *
	 * @since 3.0.0
	 *
	 * @param MetaBoxUI               $ui UI object.
	 * @param UIAwareMetaBoxRegistrar $registrar
	 *
	 * @return MetaBoxUIRegistry
	 */
	public function register_ui( MetaBoxUI $ui, UIAwareMetaBoxRegistrar $registrar ): MetaBoxUIRegistry {

		$registrar_id = $registrar->identify_for_ui();

		$registrar_ui_ids = array_key_exists( $registrar_id, $this->objects ) ? $this->objects[ $registrar_id ] : [];

		$id = $ui->id();

		if ( array_key_exists( $id, $registrar_ui_ids ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					"Unable to register meta box UI. A user interface with the '%s' already exists for registrar '%s'.",
					$id,
					$registrar_id
				)
			);
		}

		$this->objects[ $registrar_id ] = $registrar_ui_ids;

		$this->objects[ $registrar_id ][ $id ] = $ui;

		if ( ! array_key_exists( $registrar_id, $this->names ) ) {
			$this->names[ $registrar_id ] = [];
		}

		$this->names[ $registrar_id ][ $id ] = $ui->name();

		return $this;
	}

	/**
	 * Returns the selected UI as object.
	 *
	 * @since 3.0.0
	 *
	 * @param UIAwareMetaBoxRegistrar $registrar
	 *
	 * @return MetaBoxUI Selected UI object.
	 */
	public function selected_ui( UIAwareMetaBoxRegistrar $registrar ): MetaBoxUI {

		$registrar_id = $registrar->identify_for_ui();

		if ( array_key_exists( $registrar_id, $this->selected_ui ) ) {
			return $this->selected_ui[ $registrar_id ];
		}

		$ui_objects = $this->get_objects( $registrar );

		/**
		 * Filters the UI to be used for the post translation meta box.
		 *
		 * @since 3.0.0
		 *
		 * @param MetaBoxUI|null          $selected_ui Currently selected UI object.
		 * @param MetaBoxUI[]             $ui_objects  An array with all meta box IDs as keys and the objects as values.
		 * @param UIAwareMetaBoxRegistrar $registrar   Target meta box registrar object
		 */
		$user_ui = apply_filters( self::FILTER_SELECT_UI, null, $ui_objects, $registrar );

		// Let's check that selected UI is a valid MetaBoxUI object and that its id is one of the UIs for the registrar
		$ui_selected =
			$user_ui instanceof MetaBoxUI
			&& array_key_exists( $user_ui->id(), $this->get_ids( $registrar) );

		$this->selected_ui[ $registrar_id ] = $ui_selected ? $user_ui : new NullMetaBoxUI();

		if ( $ui_selected ) {

			/**
			 * Fires right after the UI for the meta box has been selected.
			 *
			 * @since 3.0.0
			 *
			 * @param MetaBoxUI               $selected_ui Selected UI object.
			 * @param UIAwareMetaBoxRegistrar $registrar   Target registrar object.
			 */
			do_action( self::ACTION_UI_SELECTED, $this->selected_ui[ $registrar_id ], $registrar );

			/**
			 * Fires right after the UI for the meta box has been selected.
			 *
			 * @since 3.0.0
			 *
			 * @param MetaBoxUI $selected_ui Selected UI object.
			 */
			do_action( self::ACTION_UI_SELECTED . "_{$registrar_id}", $this->selected_ui[ $registrar_id ] );
		}

		return $this->selected_ui[ $registrar_id ];
	}
}
