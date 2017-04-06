<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class PostTranslationMetaboxUIRegistry {

	const DEFAULT_UI = [
		PostTranslationMetaboxAdvancedUI::class,
		PostTranslationMetaboxSimpleUI::class,
	];

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
	 * @var PostTranslationMetaboxUI
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
	 * Setup view and updater on proper hooks using the selected UI.
	 */
	public function setup() {

		static $done;

		if ( $done ) {
			return;
		}

		$done = true;

		$this->ensure_default();

		add_action( 'multilingualpress.add_translation_meta_boxes', function() {
			$this->selected_ui()->setup_view();
		} );

		add_action( 'multilingualpress.save_translation_meta_boxes', function() {
			$this->selected_ui()->setup_updater();
		} );
	}

	/**
	 * @param PostTranslationMetaboxUI $ui
	 */
	public function register_ui( PostTranslationMetaboxUI $ui ) {

		$id = $ui->id();

		$this->ui[ $id ]     = $ui;
		$this->titles[ $id ] = $ui->title();

	}

	/**
	 * @return string[]
	 */
	public function all_ui_titles(): array {

		$this->ensure_default();

		return $this->titles;
	}

	/**
	 * @return string[]
	 */
	public function all_ui_ids(): array {

		$this->ensure_default();

		return array_keys( $this->ui );
	}

	/**
	 * @return PostTranslationMetaboxUI[]
	 */
	public function all_ui(): array {

		$this->ensure_default();

		return $this->ui;
	}

	/**
	 * In no ui are setup, add the defaults.
	 */
	private function ensure_default() {

		if ( $this->ui ) {
			return;
		}

		foreach ( self::DEFAULT_UI as $ui_class ) {
			$this->register_ui( new $ui_class() );
		}
	}

	/**
	 * @return PostTranslationMetaboxUI
	 */
	private function selected_ui(): PostTranslationMetaboxUI {

		if ( $this->selected_ui ) {
			return $this->selected_ui;
		}

		$this->ensure_default();

		$selected = $this->selected_ui_id && array_key_exists( $this->selected_ui_id, $this->ui )
			? $this->ui[ $this->selected_ui_id ]
			: $this->ui[ PostTranslationMetaboxSimpleUI::ID ];

		/**
		 * Filters the post metabox UI to be used
		 *
		 * @param \WP_Post $ui          Currently Selected UI.
		 * @param int      $ui_registry UI registry.
		 */
		$filtered = apply_filters( 'multilingualpress.select_translation_meta_box_ui', $selected, $this );

		$this->selected_ui = $filtered instanceof PostTranslationMetaboxUI ? $filtered : $selected;

		/**
		 * Runs after the translation UI has been selected.
		 *
		 * @param \WP_Post $post Post object.
		 */
		do_action( 'multilingualpress.translation_meta_box_ui', $this->selected_ui );

		return $this->selected_ui;
	}

}