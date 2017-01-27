<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\CustomPostTypeSupport;

use WP_Post_Type;

/**
 * Interface for all post type repository implementations.
 *
 * @package Inpsyde\MultilingualPress\Module\CustomPostTypeSupport
 * @since   3.0.0
 */
interface PostTypeRepository {

	/**
	 * Setting value.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const CPT_ACTIVE = 1;

	/**
	 * Setting value.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const CPT_INACTIVE = 0;

	/**
	 * Setting value.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const CPT_QUERY_BASED = 2;

	/**
	 * Option for storing the post type setting.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const OPTION = 'inpsyde_multilingual_cpt';

	/**
	 * Settings key for storing the post type support settings.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SETTINGS_KEY = 'post_types';

	/**
	 * Returns all custom post types that MultilingualPress is able to support.
	 *
	 * @since 3.0.0
	 *
	 * @return WP_Post_Type[] All custom post types that MultilingualPress is able to support.
	 */
	public function get_custom_post_types();

	/**
	 * Returns the slugs of all currently supported post types.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The slugs of all currently supported post types.
	 */
	public function get_supported_post_types();

	/**
	 * Checks if the given post type is active and set to be query-based.
	 *
	 * @since 3.0.0
	 *
	 * @param string $post_type Post type.
	 *
	 * @return bool Whether or not the given post type is active and set to be query-based.
	 */
	public function is_post_type_active_and_query_based( $post_type );

	/**
	 * Sets post type support to the given post types.
	 *
	 * @since 3.0.0
	 *
	 * @param array $post_types Post type slugs.
	 *
	 * @return bool Whether the support for all given post types was set successfully.
	 */
	public function set_supported_post_types( array $post_types );

	/**
	 * Removes the support for all post types.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the support for all post types was removed successfully.
	 */
	public function unsupport_all_post_types();
}
