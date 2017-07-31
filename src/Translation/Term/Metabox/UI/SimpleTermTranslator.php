<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox\UI;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\SourceTermSaveContext;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\TranslationMetaBoxView;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\TranslationMetadataUpdater;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\ViewInjection;

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
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ContentRelations $content_relations
	 * @param ServerRequest    $server_request
	 */
	public function __construct( ContentRelations $content_relations, ServerRequest $server_request ) {

		$this->content_relations = $content_relations;

		$this->server_request = $server_request;
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
		) {

			$updater = new SimpleTermTranslatorUpdater(
				$this->content_relations,
				$server_request,
				$save_context
			);

			return $updater->update( $remote_term, $remote_site_id );

		}, 30, 5 );
	}

	/**
	 * Registers the view of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_view() {

		$this->inject_into_view( function (
			\WP_Term $term,
			int $remote_site_id,
			string $remote_language,
			array $data,
			\WP_Term $remote_term = null
		) {

			$fields = new SimpleTermTranslatorFields( ! empty( $data['update'] ) );

			echo $fields->main_fields( $remote_site_id, $term, $remote_term );

		}, TranslationMetaBoxView::POSITION_MAIN );
	}
}
