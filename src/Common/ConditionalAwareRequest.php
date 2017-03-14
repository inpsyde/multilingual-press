<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

/**
 * Request implementation aware of WordPress conditional query tags.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
final class ConditionalAwareRequest implements Request {

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
	 */
	public function __construct() {

		$this->callbacks = [
			Request::TYPE_ADMIN             => 'is_admin',
			Request::TYPE_FRONT_PAGE        => 'is_front_page',
			Request::TYPE_POST_TYPE_ARCHIVE => 'is_post_type_archive',
			Request::TYPE_SEARCH            => 'is_search',
			Request::TYPE_SINGULAR          => [ $this, 'is_singular' ],
			Request::TYPE_TERM_ARCHIVE      => [ $this, 'is_term_archive' ],
		];
	}

	/**
	 * Returns the (first) post type of the current request.
	 *
	 * @since 3.0.0
	 *
	 * @return string The (first) post type, or empty string if not applicable.
	 */
	public function post_type() {

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
	public function queried_object_id() {

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
	public function type() {

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

	/** @noinspection PhpUnusedPrivateMethodInspection
	 * Checks if the current request is for the page for posts.
	 *
	 * @return bool Whether or not the current request is for the page for posts.
	 */
	private function is_page_for_posts() {

		return is_home() && ! is_front_page();
	}

	/** @noinspection PhpUnusedPrivateMethodInspection
	 * Checks if the current request is for a single post or the page for posts.
	 *
	 * @return bool Whether or not the current request is for a single post or the page for posts.
	 */
	private function is_singular() {

		return is_singular() || $this->is_page_for_posts();
	}

	/** @noinspection PhpUnusedPrivateMethodInspection
	 * Checks if the current request is for a term archive.
	 *
	 * @return bool Whether or not the current request is for a term archive.
	 */
	private function is_term_archive() {

		$queried_object = get_queried_object();

		return isset( $queried_object->taxonomy, $queried_object->name );
	}
}
