<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\Search;

use Inpsyde\MultilingualPress\Translation\Post\RelationshipContext;

/**
 * Relationship control search results view implementation displaying the post status of unpublished posts.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\Search
 * @since   3.0.0
 */
final class StatusAwareSearchResultsView implements SearchResultsView {

	/**
	 * @var Search
	 */
	private $search;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Search $search Search object.
	 */
	public function __construct( Search $search ) {

		$this->search = $search;
	}

	/**
	 * Renders the markup for the search results according to the given context.
	 *
	 * @since 3.0.0
	 *
	 * @param RelationshipContext $context Relationship context data object.
	 *
	 * @return void
	 */
	public function render( RelationshipContext $context ) {

		$posts = $this->search->get_posts( $context );
		if ( ! $posts ) {
			echo '<li>' . esc_html__( 'Nothing found.', 'multilingualpress' ) . '</li>';

			return;
		}

		$site_id = $context->remote_site_id();

		array_walk( $posts, function ( \WP_Post $post ) use ( $site_id ) {

			printf(
				'<li><label for="%4$s"><input type="radio" name="%2$s" value="%3$d" id="%4$s"> %1$s</label></li>',
				$this->get_post_title( $post ),
				esc_attr( "mlp_add_post[{$site_id}]" ),
				esc_attr( $post->ID ),
				esc_attr( "mlp-rc-search-result-{$site_id}-{$post->ID}" )
			);
		} );
	}

	/**
	 * Returns the title of the given post, including the status if not published.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return string Post title, including status if not published.
	 */
	private function get_post_title( \WP_Post $post ): string {

		if ( 'publish' === $post->post_status ) {
			return (string) esc_html( $post->post_title );
		}

		/* translators: 1: post title, 2: post status */
		$format = esc_html__( '%1$s &mdash; %2$s', 'multilingualpress' );

		return sprintf(
			$format,
			$post->post_title,
			$this->get_translated_status( $post->post_status )
		);
	}

	/**
	 * Returns the according translation of the given post status, if available.
	 *
	 * @param string $status Post status.
	 *
	 * @return string The according translation of the given post status.
	 */
	private function get_translated_status( string $status ): string {

		static $cache;
		if ( ! $cache ) {
			$cache = get_post_statuses();
		}

		return (string) ( $cache[ $status ] ?? esc_html( $status ) );
	}
}
