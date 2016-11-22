<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Relations\Post\Search;

use Inpsyde\MultilingualPress\Relations\Post\RelationshipContext;

/**
 * Interface for all relationship control search results view implementations.
 *
 * @package Inpsyde\MultilingualPress\Relations\Post\Search
 * @since   3.0.0
 */
interface SearchResultsView {

	/**
	 * Renders the markup for the search results according to the given context.
	 *
	 * @since 3.0.0
	 *
	 * @param RelationshipContext $context Relationship context data object.
	 *
	 * @return void
	 */
	public function render( RelationshipContext $context );
}
