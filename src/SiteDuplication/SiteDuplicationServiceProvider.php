<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingMultiView;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingsSectionView;
use Inpsyde\MultilingualPress\Core\Admin\NewSiteSettings;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Service provider for all site duplication objects.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
final class SiteDuplicationServiceProvider implements BootstrappableServiceProvider {

	/**
	 * Registers the provided services on the given container.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function register( Container $container ) {

		$container['multilingualpress.active_plugins'] = function () {

			return new ActivePlugins();
		};

		$container['multilingualpress.attachment_copier'] = function ( Container $container ) {

			return new WPDBAttachmentCopier(
				$container['multilingualpress.wpdb'],
				$container['multilingualpress.base_path_adapter'],
				$container['multilingualpress.table_string_replacer']
			);
		};

		$container['multilingualpress.site_duplication_activate_plugins_setting'] = function () {

			return new ActivatePluginsSetting();
		};

		$container['multilingualpress.site_duplication_based_on_site_setting'] = function ( Container $container ) {

			return new BasedOnSiteSetting(
				$container['multilingualpress.wpdb']
			);
		};

		$container['multilingualpress.site_duplication_search_engine_visibility_setting'] = function () {

			return new SearchEngineVisibilitySetting();
		};

		$container['multilingualpress.site_duplication_settings_view'] = function ( Container $container ) {

			return SiteSettingMultiView::from_view_models( [
				$container['multilingualpress.site_duplication_based_on_site_setting'],
				$container['multilingualpress.site_duplication_activate_plugins_setting'],
				$container['multilingualpress.site_duplication_search_engine_visibility_setting'],
			] );
		};

		$container['multilingualpress.site_duplicator'] = function ( Container $container ) {

			return new SiteDuplicator(
				$container['multilingualpress.wpdb'],
				$container['multilingualpress.table_list'],
				$container['multilingualpress.table_duplicator'],
				$container['multilingualpress.table_replacer'],
				$container['multilingualpress.active_plugins'],
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.attachment_copier'],
				$container['multilingualpress.server_request']
			);
		};
	}

	/**
	 * Bootstraps the registered services.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function bootstrap( Container $container ) {

		add_action( 'wpmu_new_blog', [ $container['multilingualpress.site_duplicator'], 'duplicate_site' ], 0 );

		add_action(
			SiteSettingsSectionView::ACTION_AFTER . '_' . NewSiteSettings::ID,
			[ $container['multilingualpress.site_duplication_settings_view'], 'render' ]
		);
	}
}
