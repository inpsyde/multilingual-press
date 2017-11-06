<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core;

/**
 * Interface for all post type repository implementations.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
interface PostTypeRepository {

	/**
	 * Post type slugs.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
	const DEFAULT_SUPPORTED_POST_TYPES = [
		'page',
		'post',
	];

	/**
	 * Settings field name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FIELD_ACTIVE = 'active';

	/**
	 * Settings field name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FIELD_PERMALINK = 'permalink';

	/**
	 * Settings field name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FIELD_UI = 'ui';

	/**
	 * Option for storing the post type setting.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const OPTION = 'inpsyde_multilingual_cpt';

	/**
	 * Returns all post types that MultilingualPress is able to support.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Post_Type[] All post types that MultilingualPress is able to support.
	 */
	public function get_available_post_types(): array;

	/**
	 * Returns the UI ID of the post type with the given slug.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Post type slug.
	 *
	 * @return string Post type UI ID.
	 */
	public function get_post_type_ui( string $slug ): string;

	/**
	 * Checks if the post type with the given slug is active.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Post type slug.
	 *
	 * @return bool Whether or not the given post type is active.
	 */
	public function is_post_type_active( string $slug ): bool;

	/**
	 * Checks if the post type with the given slug is set to be query-based.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Post type slug.
	 *
	 * @return bool Whether or not the given post type is set to be query-based.
	 */
	public function is_post_type_query_based( string $slug ): bool;

	/**
	 * Sets post type support to the given post types.
	 *
	 * @since 3.0.0
	 *
	 * @param array $post_types Post type slugs.
	 *
	 * @return bool Whether the support for all given post types was set successfully.
	 */
	public function set_supported_post_types( array $post_types ): bool;

	/**
	 * Removes the support for all post types.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the support for all post types was removed successfully.
	 */
	public function unset_supported_post_types(): bool;
}
