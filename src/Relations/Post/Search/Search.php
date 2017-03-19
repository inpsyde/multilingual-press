<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Relations\Post\Search;

use Inpsyde\MultilingualPress\Relations\Post\RelationshipContext;

/**
 * Interface for all search implementations.
 *
 * @package Inpsyde\MultilingualPress\Relations\Post\Search
 * @since   3.0.0
 */
interface Search {

	/**
	 * Argument name to be used in order to denote the search in requests.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ARG_NAME = 's';

	/**
	 * Returns the latest/best-matching posts with respect to the given context.
	 *
	 * @since 3.0.0
	 *
	 * @param RelationshipContext $context Relationship context data object.
	 *
	 * @return \WP_Post[] The latest/best-matching posts.
	 */
	public function get_posts( RelationshipContext $context ): array;
}
