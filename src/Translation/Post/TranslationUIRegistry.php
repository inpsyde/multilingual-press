<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class TranslationUIRegistry {

	const FILTER_SELECT_UI = 'multilingualpress.select_translation_meta_box_ui';

	const ACTION_UI_SELECTED = 'multilingualpress.translation_meta_box_ui';

	/**
	 * @var array
	 */
	private $ui = [];

	/**
	 * @var array
	 */
	private $titles = [];

	/**
	 * @var string
	 */
	private $selected_ui_id;

	/**
	 * @var TranslationMetaboxUI
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

		add_action( PostMetaboxRegistrar::ACTION_ADD_BOXES, function () {

			$this->selected_ui()->setup_view();
		} );

		add_action( PostMetaboxRegistrar::ACTION_SAVE_BOXES, function () {

			$this->selected_ui()->setup_updater();
		} );
	}

	/**
	 * @param TranslationMetaboxUI $ui
	 *
	 * @return TranslationUIRegistry
	 */
	public function register_ui( TranslationMetaboxUI $ui ): TranslationUIRegistry {

		$id = $ui->id();

		if ( array_key_exists( $id, $this->ui ) ) {
			throw new \InvalidArgumentException(
				"It is not possible to use '{$id}' as post metabox UI identifier because it is already in use."
			);
		}

		$this->ui[ $id ]     = $ui;
		$this->titles[ $id ] = $ui->title();

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function all_ui_titles(): array {

		return $this->titles;
	}

	/**
	 * @return string[]
	 */
	public function all_ui_ids(): array {

		return array_keys( $this->ui );
	}

	/**
	 * @return TranslationMetaboxUI[]
	 */
	public function all_ui(): array {

		return $this->ui;
	}

	/**
	 * @return TranslationMetaboxUI
	 */
	private function selected_ui(): TranslationMetaboxUI {

		if ( $this->selected_ui ) {
			return $this->selected_ui;
		}

		$selected = $this->selected_ui_id && array_key_exists( $this->selected_ui_id, $this->ui )
			? $this->ui[ $this->selected_ui_id ]
			: null;

		if ( ! $selected ) {
			$selected = array_key_exists( TranslationAdvancedUI::ID, $this->ui )
				? $this->ui[ TranslationAdvancedUI::ID ]
				: new TranslationAdvancedUI();
		}

		/**
		 * Filters the post metabox UI to be used
		 *
		 * @param \WP_Post $ui Currently selected UI object.
		 * @param array    $ui Array of available UI where keys are UI ids and values are UI titles
		 */
		$filtered = apply_filters( self::FILTER_SELECT_UI, $selected, $this->titles );

		$this->selected_ui = $filtered instanceof TranslationMetaboxUI ? $filtered : $selected;

		/**
		 * Runs after the translation UI has been selected.
		 *
		 * @param \WP_Post $post Post object.
		 */
		do_action( self::ACTION_UI_SELECTED, $this->selected_ui );

		return $this->selected_ui;
	}

}