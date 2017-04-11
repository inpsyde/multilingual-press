<?php # -*- coding: utf-8 -*-

/**
 * TODO: Discuss whether or not to make this class not specific to posts.
 *      Currently, it both references and contains post-specific hooks, it falls back to the advanced post translator
 *      UI, and might also instantiate one.
 */

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUIRegistry;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI\AdvancedPostTranslator;

/**
 * Meta box UI registry implementation for post translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post
 * @since   3.0.0
 */
final class TranslationUIRegistry implements MetaBoxUIRegistry {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_UI_SELECTED = 'multilingualpress.post_translation_meta_box_ui';

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_SELECT_UI = 'multilingualpress.select_post_translation_meta_box_ui';

	/**
	 * @var string[]
	 */
	private $names = [];

	/**
	 * @var MetaBoxUI
	 */
	private $selected_ui;

	/**
	 * @var string
	 */
	private $selected_ui_id;

	/**
	 * @var MetaBoxUI[]
	 */
	private $objects = [];

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $selected_ui_id Optional. ID of the selected UI, if exists. Defaults to advanced post translator.
	 */
	public function __construct( string $selected_ui_id = AdvancedPostTranslator::ID ) {

		$this->selected_ui_id = $selected_ui_id;
	}

	/**
	 * Returns an array with all meta box IDs.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] An array with all meta box IDs.
	 */
	public function get_ids(): array {

		return array_keys( $this->objects );
	}

	/**
	 * Returns an array with all meta box names.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] An array with all meta box IDs as keys and the names as values.
	 */
	public function get_names(): array {

		return $this->names;
	}

	/**
	 * Returns an array with all meta box objects.
	 *
	 * @since 3.0.0
	 *
	 * @return MetaBoxUI[] An array with all meta box IDs as keys and the objects as values.
	 */
	public function get_objects(): array {

		return $this->objects;
	}

	/**
	 * Registers both the meta box view and the metadata updater of the selected UI for usage.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the registration was successful.
	 */
	public function register(): bool {

		static $done;
		if ( $done ) {
			return false;
		}

		$done = true;

		add_action( PostMetaBoxRegistrar::ACTION_ADD_META_BOXES, function () {

			$this->selected_ui()->register_view();
		} );

		add_action( PostMetaBoxRegistrar::ACTION_SAVE_META_BOXES, function () {

			$this->selected_ui()->register_updater();
		} );

		return true;
	}

	/**
	 * Registers the given meta box UI.
	 *
	 * @since 3.0.0
	 *
	 * @param MetaBoxUI $ui UI object.
	 *
	 * @return MetaBoxUIRegistry
	 */
	public function register_ui( MetaBoxUI $ui ): MetaBoxUIRegistry {

		$id = $ui->id();

		if ( array_key_exists( $id, $this->objects ) ) {
			throw new \InvalidArgumentException(
				"Unable to register meta box UI. A user interface with the '{$id}' already exists."
			);
		}

		$this->names[ $id ] = $ui->name();

		$this->objects[ $id ] = $ui;

		return $this;
	}

	/**
	 * Returns the selected UI as object.
	 *
	 * @return MetaBoxUI Selected UI object.
	 */
	private function selected_ui(): MetaBoxUI {

		if ( $this->selected_ui ) {
			return $this->selected_ui;
		}

		$selected_ui = $this->selected_ui_id && array_key_exists( $this->selected_ui_id, $this->objects )
			? $this->objects[ $this->selected_ui_id ]
			: new AdvancedPostTranslator();

		/**
		 * Filters the UI to be used for the post translation meta box.
		 *
		 * @since 3.0.0
		 *
		 * @param MetaBoxUI   $selected_ui Currently selected UI object.
		 * @param MetaBoxUI[] $objects     An array with all meta box IDs as keys and the objects as values.
		 */
		$user_ui = apply_filters( self::FILTER_SELECT_UI, $selected_ui, $this->objects );

		$this->selected_ui = $user_ui instanceof MetaBoxUI ? $user_ui : $selected_ui;

		/**
		 * Fires right after the UI for the post translation meta box has been selected.
		 *
		 * @since 3.0.0
		 *
		 * @param MetaBoxUI $selected_ui Selected UI object.
		 */
		do_action( self::ACTION_UI_SELECTED, $this->selected_ui );

		return $this->selected_ui;
	}
}
