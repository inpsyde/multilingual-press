<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxView;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetadataUpdater;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;
use Inpsyde\MultilingualPress\Translation\Term\ActiveTaxonomies;

/**
 * Meta box controller implementation for term translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term\MetaBox
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
	const ACTION_INITIALIZED_UPDATER = 'multilingualpress.term_translation_updater';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_INITIALIZED_VIEW = 'multilingualpress.term_translation_updater';

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * @var ActiveTaxonomies
	 */
	private $active_taxonomies;

	/**
	 * @var \WP_Term
	 */
	private $remote_term;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int              $site_id           Site ID.
	 * @param SiteRelations    $site_relations    Site relations object.
	 * @param ActiveTaxonomies $active_taxonomies Active taxonomies object.
	 * @param \WP_Term         $remote_term       Optional. Term object. Defaults to null.
	 */
	public function __construct(
		int $site_id,
		SiteRelations $site_relations,
		ActiveTaxonomies $active_taxonomies,
		\WP_Term $remote_term = null
	) {

		$this->site_id = $site_id;

		$this->site_relations = $site_relations;

		$this->active_taxonomies = $active_taxonomies;

		$this->remote_term = $remote_term;
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

		return new TranslationMetaBox( $this->site_id, $this->active_taxonomies, $this->remote_term );
	}

	/**
	 * Returns the metadata updater instance for the meta box.
	 *
	 * @since 3.0.0
	 *
	 * @return MetadataUpdater
	 */
	public function updater(): MetadataUpdater {

		$updater = new TranslationMetadataUpdater(
			$this->site_id,
			$this->site_relations,
			$this->active_taxonomies,
			$this->remote_term
		);

		/**
		 * Fires right after the term translation metadata updater was initialized.
		 *
		 * Hook here to pass custom data.
		 *
		 * @since 3.0.0
		 *
		 * @param TranslationMetadataUpdater $updater Updater object.
		 * @param int                        $site_id Remote site id.
		 * @param \WP_Term|null              $term    Remote term object, if any, null otherwise.
		 */
		do_action( self::ACTION_INITIALIZED_UPDATER, $updater, $this->site_id, $this->remote_term );

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

		$view = new TranslationMetaBoxView( $this->site_id, $this->remote_term );

		/**
		 * Fires right after the term translation view was initialized.
		 *
		 * Hook here to pass custom data.
		 *
		 * @since 3.0.0
		 *
		 * @param TranslationMetaBoxView $view    View object.
		 * @param int                    $site_id Remote site id.
		 * @param \WP_Term|null          $term    Remote term object, if any, null otherwise.
		 */
		do_action( self::ACTION_INITIALIZED_VIEW, $view, $this->site_id, $this->remote_term );

		return $view;
	}
}
