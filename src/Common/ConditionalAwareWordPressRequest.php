<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common;

/**
 * Request implementation aware of WordPress conditional query tags.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
final class ConditionalAwareWordPressRequest implements WordPressRequest {

	/**
	 * @var callable[]
	 */
	private $callbacks;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @see ConditionalAwareWordPressRequest::is_singular()
	 * @see ConditionalAwareWordPressRequest::is_term_archive()
	 */
	public function __construct() {

		$this->callbacks = [
			WordPressRequest::TYPE_ADMIN             => 'is_admin',
			WordPressRequest::TYPE_FRONT_PAGE        => 'is_front_page',
			WordPressRequest::TYPE_POST_TYPE_ARCHIVE => 'is_post_type_archive',
			WordPressRequest::TYPE_SEARCH            => 'is_search',
			WordPressRequest::TYPE_SINGULAR          => [ $this, 'is_singular' ],
			WordPressRequest::TYPE_TERM_ARCHIVE      => [ $this, 'is_term_archive' ],
		];
	}

	/**
	 * Returns the (first) post type of the current request.
	 *
	 * @since 3.0.0
	 *
	 * @return string The (first) post type, or empty string if not applicable.
	 */
	public function post_type(): string {

		$post_type = (array) get_query_var( 'post_type' );

		return (string) reset( $post_type );
	}

	/**
	 * Returns the ID of the queried object.
	 *
	 * For term archives, this is the term taxonomy ID (not the term ID).
	 *
	 * @since 3.0.0
	 *
	 * @return int The ID of the queried object.
	 */
	public function queried_object_id(): int {

		if ( is_category() || is_tag() || is_tax() ) {
			$queried_object = get_queried_object();

			return (int) ( $queried_object->term_taxonomy_id ?? 0 );
		}

		return (int) get_queried_object_id();
	}

	/**
	 * Returns the type of the current request.
	 *
	 * @since 3.0.0
	 *
	 * @return string Request type, or empty string on failure.
	 */
	public function type(): string {

		if ( isset( $this->type ) ) {
			return $this->type;
		}

		$this->type = '';

		foreach ( $this->callbacks as $type => $callback ) {
			if ( $callback() ) {
				$this->type = $type;
				break;
			}
		}

		return $this->type;
	}

	/**
	 * Checks if the current request is for the page for posts.
	 *
	 * @return bool Whether or not the current request is for the page for posts.
	 */
	private function is_page_for_posts(): bool {

		return is_home() && ! is_front_page();
	}

	/**
	 * Checks if the current request is for a single post or the page for posts.
	 *
	 * @return bool Whether or not the current request is for a single post or the page for posts.
	 */
	private function is_singular(): bool {

		return is_singular() || $this->is_page_for_posts();
	}

	/**
	 * Checks if the current request is for a term archive.
	 *
	 * @return bool Whether or not the current request is for a term archive.
	 */
	private function is_term_archive(): bool {

		$queried_object = get_queried_object();

		return isset( $queried_object->taxonomy, $queried_object->name );
	}
}
