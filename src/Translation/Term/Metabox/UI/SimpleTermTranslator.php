<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox\UI;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\SourceTermSaveContext;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\TranslationMetaBoxView;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\TranslationMetadataUpdater;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\ViewInjection;
use Inpsyde\MultilingualPress\Translation\Term\TermOptionsRepository;

/**
 * @package Inpsyde\MultilingualPress\Translation\Term\MetaBox\UI
 * @since   3.0.0
 */
final class SimpleTermTranslator implements MetaBoxUI {

	use ViewInjection;

	/**
	 * User interface ID.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ID = 'multilingualpress.simple_term_translator';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var TermOptionsRepository
	 */
	private $repository;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ContentRelations      $content_relations
	 * @param ServerRequest         $server_request
	 * @param TermOptionsRepository $repository
	 * @param AssetManager          $asset_manager
	 */
	public function __construct(
		ContentRelations $content_relations,
		ServerRequest $server_request,
		TermOptionsRepository $repository,
		AssetManager $asset_manager
	) {

		$this->content_relations = $content_relations;

		$this->server_request = $server_request;

		$this->repository = $repository;

		$this->asset_manager = $asset_manager;
	}

	/**
	 * Returns the ID of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string ID of the user interface.
	 */
	public function id(): string {

		return self::ID;
	}

	/**
	 * Initializes the user interface.
	 *
	 * This will be called early to allow wiring up of early-running hooks, for example, 'wp_ajax_{$action}'.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function initialize() {

	}

	/**
	 * Returns the name of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string Name of the user interface.
	 */
	public function name(): string {

		return _x( 'Simple', 'Term translation UI name', 'multilingualpress' );
	}

	/**
	 * Registers the updater of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_updater() {

		add_filter( TranslationMetadataUpdater::FILTER_SAVE_TERM, function (
			\WP_Term $remote_term,
			int $remote_site_id,
			ServerRequest $server_request,
			SourceTermSaveContext $save_context
		): \WP_Term {

			$updater = new SimpleTermTranslatorUpdater(
				$this->content_relations,
				$server_request,
				$save_context
			);

			return $updater->update( $remote_site_id, $remote_term );
		}, 30, 4 );
	}

	/**
	 * Registers the view of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_view() {

		$fields = new SimpleTermTranslatorFields( $this->server_request, $this->repository, $this->asset_manager );

		$this->inject_into_view( function (
			/** @noinspection PhpUnusedParameterInspection */
			\WP_Term $term,
			int $remote_site_id,
			string $remote_language,
			\WP_Term $remote_term = null,
			array $data = []
		) use ( $fields ) {

			$fields->set_update( ! empty( $data['update'] ) );

			$fields->render_main_fields( $term, $remote_site_id, $remote_term );
		}, TranslationMetaBoxView::POSITION_MAIN );
	}
}
