<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\Admin;

/**
 * Interface for all site settings repository implementations.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
interface SiteSettingsRepository {

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_ALTERNATIVE_LANGUAGE_TITLE = 'mlp_alternative_language_title';

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_FLAG_IMAGE_URL = 'mlp_flag_image_url';

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_LANGUAGE = 'mlp_site_language';

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_RELATIONSHIPS = 'mlp_site_relations';
}
