<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetadataUpdater;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Term\TermMetaUpdater;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Term\ActiveTaxonomies;

/**
 * Metadata updater implementation for term translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term\MetaBox
 * @since   3.0.0
 */
final class TranslationMetadataUpdater implements TermMetaUpdater {

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_SAVE_TERM = 'multilingualpress.term_translation_meta_box_save';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_SAVED_TERM = 'multilingualpress.term_translation_meta_box_saved';

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * @var int
	 */
	private $remote_site_id;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var ActiveTaxonomies
	 */
	private $active_taxonomies;

	/**
	 * @var \WP_Term
	 */
	private $remote_term;

	/**
	 * @var SourceTermSaveContext
	 */
	private $save_context;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int              $site_id           Site ID.
	 * @param SiteRelations    $site_relations    Site relations object.
	 * @param ActiveTaxonomies $active_taxonomies Active taxonomies object.
	 * @param \WP_Term         $remote_term       Optional. Remote term object. Defaults to null.
	 */
	public function __construct(
		int $site_id,
		SiteRelations $site_relations,
		ActiveTaxonomies $active_taxonomies,
		\WP_Term $remote_term = null
	) {

		$this->remote_site_id = $site_id;

		$this->site_relations = $site_relations;

		$this->active_taxonomies = $active_taxonomies;

		$this->remote_term = $remote_term;
	}

	/**
	 * Returns an instance with the given data.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Data to be set.
	 *
	 * @return MetadataUpdater
	 */
	public function with_data( array $data ): MetadataUpdater {

		$this->data = array_merge( $this->data, $data );

		return $this;
	}

	/**
	 * Returns an instance with the given term.
	 *
	 * @since 3.0.0
	 *
	 * @param SourceTermSaveContext $save_context Save context object to set.
	 *
	 * @return TermMetaUpdater
	 */
	public function with_term_save_context( SourceTermSaveContext $save_context ): TermMetaUpdater {

		$this->save_context = $save_context;

		return $this;
	}

	/**
	 * Updates the metadata included in the given server request.
	 *
	 * The update happen in the context of remote term site.
	 *
	 * @since 3.0.0
	 *
	 * @param ServerRequest $server_request Server request object.
	 *
	 * @return bool True when update is successful.
	 */
	public function update( ServerRequest $server_request ): bool {

		if ( ! $this->save_context instanceof SourceTermSaveContext ) {
			return false;
		}

		if ( ! $this->remote_term ) {
			$this->remote_term = new \WP_Term( (object) [
				'taxonomy'   => $this->save_context[ SourceTermSaveContext::TAXONOMY ],
			] );
		}

		/**
		 * Filter remote term instance.
		 *
		 * Updaters from UI should hook here and return the maybe updated remote term.
		 *
		 * Returning anything but a term object or a term object with invalid ID means something failed in the update.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term              $remote_term    Remote term object being saved.
		 * @param int                   $remote_site_id Remote site ID.
		 * @param ServerRequest         $server_request Server request object.
		 * @param SourceTermSaveContext $save_context   Save context object.
		 */
		$remote_term = apply_filters(
			self::FILTER_SAVE_TERM,
			$this->remote_term,
			$this->remote_site_id,
			$server_request,
			$this->save_context
		);

		if ( ! $remote_term instanceof \WP_Term || ! $remote_term->ID ) {
			return false;
		}

		/**
		 * Action fired after remote term has been saved by updaters provided by UI.
		 *
		 * This provides access on just saved remote term alongside source save context, to allow custom saving
		 * routines, (e.g. for custom term meta) no matter the UI in use.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term              $remote_term    Remote term object being saved.
		 * @param int                   $remote_site_id Remote site ID.
		 * @param string                $source_term    Source term object.
		 * @param ServerRequest         $server_request Server request object.
		 * @param SourceTermSaveContext $save_context   Save context object.
		 */
		do_action(
			self::ACTION_SAVED_TERM,
			$this->remote_term,
			$this->remote_site_id,
			$this->save_context,
			$server_request
		);

		return true;
	}
}
