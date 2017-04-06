<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

/**
 * Interface for all request implementations.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
interface WordPressRequestContext {

	/**
	 * Request type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_ADMIN = 'admin';

	/**
	 * Request type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_FRONT_PAGE = 'front-page';

	/**
	 * Request type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_POST_TYPE_ARCHIVE = 'post-type-archive';

	/**
	 * Request type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_SEARCH = 'search';

	/**
	 * Request type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_SINGULAR = 'post';

	/**
	 * Request type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_TERM_ARCHIVE = 'term';

	/**
	 * Returns the (first) post type of the current request.
	 *
	 * @since 3.0.0
	 *
	 * @return string The (first) post type, or empty string if not applicable.
	 */
	public function post_type(): string;

	/**
	 * Returns the ID of the queried object.
	 *
	 * For term archives, this is the term taxonomy ID (not the term ID).
	 *
	 * @since 3.0.0
	 *
	 * @return int The ID of the queried object.
	 */
	public function queried_object_id(): int;

	/**
	 * Returns the type of the current request.
	 *
	 * @since 3.0.0
	 *
	 * @return string Request type, or empty string on failure.
	 */
	public function type(): string;
}
