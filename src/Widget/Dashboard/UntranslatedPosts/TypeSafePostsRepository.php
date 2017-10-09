<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use Inpsyde\MultilingualPress\Translation\Post\ActivePostTypes;

/**
 * Type-safe untranslated posts repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
final class TypeSafePostsRepository implements PostsRepository {

	/**
	 * @var ActivePostTypes
	 */
	private $active_post_types;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ActivePostTypes $active_post_types Active post types storage object.
	 */
	public function __construct( ActivePostTypes $active_post_types ) {

		$this->active_post_types = $active_post_types;
	}

	/**
	 * Returns all untranslated posts for the current site.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Post[] All untranslated posts for the current site.
	 */
	public function get_untranslated_posts(): array {

		/**
		 * Filters the untranslated posts query args.
		 *
		 * @since 3.0.0
		 *
		 * @param array $args Query args.
		 */
		$args = (array) apply_filters( PostsRepository::FILTER_QUERY_ARGS, [
			// Not suppressing filters (which is done by default when using get_posts()) makes caching possible.
			'suppress_filters' => false,
			'post_type'        => $this->active_post_types->names(),
			'post_status'      => get_post_stati( [
				'exclude_from_search' => false,
			] ),
			'meta_query'       => [
				[
					'key'     => PostsRepository::META_KEY,
					'compare' => '!=',
					'value'   => true,
				],
			],
		] );

		return (array) get_posts( $args );
	}

	/**
	 * Checks if the post with the given ID has been translated.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id Optional. Post ID. Defaults to 0.
	 *
	 * @return bool Whether or not the post with the given ID has been translated.
	 */
	public function is_post_translated( int $post_id = 0 ): bool {

		$post_id = $post_id ?: (int) get_the_ID();

		return PostsRepository::META_VALUE_UNTRANSLATED !== get_post_meta( $post_id, PostsRepository::META_KEY, true );
	}

	/**
	 * Updates the translation complete setting value for the post with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $value   Setting value to be set.
	 *
	 * @return bool Whether or not the translation complete setting value was updated successfully.
	 */
	public function update_post( int $post_id, bool $value ): bool {

		return (bool) update_post_meta(
			$post_id,
			PostsRepository::META_KEY,
			$value ?: PostsRepository::META_VALUE_UNTRANSLATED
		);
	}
}
