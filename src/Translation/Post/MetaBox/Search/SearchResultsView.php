<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\Search;

use Inpsyde\MultilingualPress\Translation\Post\RelationshipContext;

/**
 * Interface for all relationship control search results view implementations.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\Search
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
