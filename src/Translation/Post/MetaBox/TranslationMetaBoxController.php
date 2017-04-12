<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxView;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetadataUpdater;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;
use Inpsyde\MultilingualPress\Translation\Post\AllowedPostTypes;

/**
 * Meta box controller implementation for post translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox
 * @since   3.0.0
 */
final class TranslationMetaBoxController implements SiteAwareMetaBoxController {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_INITIALIZED_UPDATER = 'multilingualpress.post_translation_updater';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_INITIALIZED_VIEW = 'multilingualpress.post_translation_view';

	/**
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * @var array
	 */
	private $post_types;

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int              $site_id    Site ID.
	 * @param AllowedPostTypes $post_types Allowed post type object.
	 * @param \WP_Post         $post       Optional. Post object. Defaults to null.
	 */
	public function __construct( int $site_id, AllowedPostTypes $post_types, \WP_Post $post = null ) {

		$this->site_id = $site_id;

		$this->post_types = $post_types;

		$this->post = $post;
	}

	/**
	 * Returns the site ID for the meta box.
	 *
	 * @since 3.0.0
	 *
	 * @return int Site ID.
	 */
	public function site_id(): int {

		return $this->site_id;
	}

	/**
	 * Returns the meta box (data) instance.
	 *
	 * @since 3.0.0
	 *
	 * @return MetaBox
	 */
	public function meta_box(): MetaBox {

		return new TranslationMetaBox( $this->site_id, $this->post_types, $this->post );
	}

	/**
	 * Returns the metadata updater instance for the meta box.
	 *
	 * @since 3.0.0
	 *
	 * @return MetadataUpdater
	 */
	public function updater(): MetadataUpdater {

		$updater = new TranslationMetadataUpdater( $this->site_id, $this->post );

		/**
		 * Fires right after the post translation metadata updater was initialized.
		 *
		 * Hook here to pass custom data.
		 *
		 * @since 3.0.0
		 *
		 * @param TranslationMetadataUpdater $updater Updater object.
		 */
		do_action( self::ACTION_INITIALIZED_UPDATER, $updater );

		return $updater;
	}

	/**
	 * Returns the view instance for the meta box.
	 *
	 * @since 3.0.0
	 *
	 * @return MetaBoxView
	 */
	public function view(): MetaBoxView {

		$view = new TranslationMetaBoxView( $this->site_id, $this->post );

		/**
		 * Fires right after the post translation view was initialized.
		 *
		 * Hook here to pass custom data.
		 *
		 * @since 3.0.0
		 *
		 * @param TranslationMetaBoxView $view View object.
		 */
		do_action( self::ACTION_INITIALIZED_VIEW, $view );

		return $view;
	}
}
