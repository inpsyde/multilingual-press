<?php # -*- coding: utf-8 -*-

// TODO

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUIRegistry;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI\AdvancedPostTranslator;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;

/**
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
final class TranslationUIRegistry implements MetaBoxUIRegistry {

	const FILTER_SELECT_UI = 'multilingualpress.select_translation_meta_box_ui';

	const ACTION_UI_SELECTED = 'multilingualpress.translation_meta_box_ui';

	/**
	 * @var array
	 */
	private $ui = [];

	/**
	 * @var array
	 */
	private $names = [];

	/**
	 * @var string
	 */
	private $selected_ui_id;

	/**
	 * @var MetaBoxUI
	 */
	private $selected_ui;

	/**
	 * Constructor.
	 *
	 * @param string $selected_ui_id
	 */
	public function __construct( string $selected_ui_id = '' ) {

		$this->selected_ui_id = $selected_ui_id;
	}

	/**
	 * Setup view and updater on proper hook using the selected UI.
	 */
	public function setup() {

		static $done;
		if ( $done ) {
			return;
		}

		$done = true;

		add_action( PostMetaBoxRegistrar::ACTION_ADD_BOXES, function () {

			$this->selected_ui()->register_view();
		} );

		add_action( PostMetaBoxRegistrar::ACTION_SAVE_BOXES, function () {

			$this->selected_ui()->register_updater();
		} );
	}

	/**
	 * @param MetaBoxUI $ui
	 *
	 * @return MetaBoxUIRegistry
	 */
	public function register_ui( MetaBoxUI $ui ): MetaBoxUIRegistry {

		$id = $ui->id();

		if ( array_key_exists( $id, $this->ui ) ) {
			throw new \InvalidArgumentException(
				"It is not possible to use '{$id}' as post metabox UI identifier because it is already in use."
			);
		}

		$this->ui[ $id ] = $ui;

		$this->names[ $id ] = $ui->name();

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function all_ui_names(): array {

		return $this->names;
	}

	/**
	 * @return string[]
	 */
	public function all_ui_ids(): array {

		return array_keys( $this->ui );
	}

	/**
	 * @return MetaBoxUI[]
	 */
	public function all_ui(): array {

		return $this->ui;
	}

	/**
	 * @return MetaBoxUI
	 */
	private function selected_ui(): MetaBoxUI {

		if ( $this->selected_ui ) {
			return $this->selected_ui;
		}

		$selected = $this->selected_ui_id && array_key_exists( $this->selected_ui_id, $this->ui )
			? $this->ui[ $this->selected_ui_id ]
			: null;

		if ( ! $selected ) {
			$selected = array_key_exists( AdvancedPostTranslator::ID, $this->ui )
				? $this->ui[ AdvancedPostTranslator::ID ]
				: new AdvancedPostTranslator();
		}

		/**
		 * Filters the post metabox UI to be used
		 *
		 * @param \WP_Post $ui Currently selected UI object.
		 * @param array    $ui Array of available UI where keys are UI ids and values are UI titles
		 */
		$filtered = apply_filters( self::FILTER_SELECT_UI, $selected, $this->names );

		$this->selected_ui = $filtered instanceof MetaBoxUI ? $filtered : $selected;

		/**
		 * Runs after the translation UI has been selected.
		 *
		 * @param \WP_Post $post Post object.
		 */
		do_action( self::ACTION_UI_SELECTED, $this->selected_ui );

		return $this->selected_ui;
	}
}
