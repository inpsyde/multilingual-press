<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Relations\Post\Search;

use Inpsyde\MultilingualPress\Relations\Post\RelationshipContext;

/**
 * Request-aware search implementation.
 *
 * @package Inpsyde\MultilingualPress\Relations\Post\Search
 * @since   3.0.0
 */
final class RequestAwareSearch implements Search {

	/**
	 * Returns the latest/best-matching posts with respect to the given context.
	 *
	 * @since 3.0.0
	 *
	 * @param RelationshipContext $context Relationship context data object.
	 *
	 * @return \WP_Post[] The latest/best-matching posts.
	 */
	public function get_posts( RelationshipContext $context ): array {

		$remote_site_id = $context->remote_site_id();
		if ( ! $remote_site_id ) {
			return [];
		}

		$source_post = $context->source_post();
		if ( ! $source_post ) {
			return [];
		}

		$args = [
			'numberposts' => 10,
			'post_type'   => $source_post->post_type,
			'post_status' => [
				'draft',
				'future',
				'private',
				'publish',
			],
		];

		$remote_post_id = $context->remote_post_id();
		if ( $remote_post_id ) {
			$args['exclude'] = $remote_post_id;
		}

		$search_query = $this->get_search_query();
		if ( $search_query ) {
			$args = array_merge( $args, [
				's'       => $search_query,
				'orderby' => 'relevance',
			] );
		}

		switch_to_blog( $remote_site_id );
		$posts = (array) get_posts( $args );
		restore_current_blog();

		return $posts;
	}

	/**
	 * Returns the search query included in the request, if exists.
	 *
	 * @return string Search query.
	 */
	private function get_search_query(): string {

		return (string) ( $_REQUEST[ Search::ARG_NAME ] ?? '' );
	}
}
