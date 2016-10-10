<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\SiteDuplication;

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

		$container['multilingualpress.attachment_copier'] = function ( Container $container ) {

			return new WPDBAttachmentCopier(
				$container['multilingualpress.base_path_adapter'],
				$container['multilingualpress.table_string_replacer']
			);
		};

		$container['multilingualpress.active_plugins'] = function () {

			return new ActivePlugins();
		};

		$container['multilingualpress.site_duplication_settings_view'] = function () {

			return new SettingsView();
		};

		$container['multilingualpress.site_duplicator'] = function ( Container $container ) {

			return new SiteDuplicator(
				$container['multilingualpress.table_list'],
				$container['multilingualpress.table_duplicator'],
				$container['multilingualpress.table_replacer'],
				$container['multilingualpress.active_plugins'],
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.attachment_copier']
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

		add_action( 'wpmu_new_blog', [ $container['multilingualpress.site_duplicator'], 'duplicate_site' ] );

		add_action(
			'mlp_after_new_blog_fields',
			[ $container['multilingualpress.site_duplication_settings_view'], 'render' ]
		);
	}
}
