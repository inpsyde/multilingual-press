<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Relations\Post\Search;

use Inpsyde\MultilingualPress\Relations\Post\RelationshipContext;

/**
 * Controller for AJAX-based search requests.
 *
 * @package Inpsyde\MultilingualPress\Relations\Post\Search
 * @since   3.0.0
 */
class SearchController {

	/**
	 * Action to be used in requests.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION = 'mlp_rc_live_search';

	/**
	 * @var SearchResultsView
	 */
	private $search_results;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SearchResultsView $search_results SearchResultsView object.
	 */
	public function __construct( SearchResultsView $search_results ) {

		$this->search_results = $search_results;
	}

	/**
	 * Initializes the AJAX-based live search.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function initialize() {

		add_action( 'wp_ajax_' . static::ACTION, function () {

			$context = RelationshipContext::from_request();

			ob_start();

			$this->search_results->render( $context );

			wp_send_json_success( [
				'html'         => ob_get_clean(),
				'remoteSiteId' => $context->remote_site_id(),
			] );
		} );
	}
}
