<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget;

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Widget service provider.
 *
 * @package Inpsyde\MultilingualPress\Widget
 * @since   3.0.0
 */
final class WidgetServiceProvider implements BootstrappableServiceProvider {

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

		$container['multilingualpress.language_switcher_widget'] = function ( Container $container ) {

			return new Sidebar\LanguageSwitcher\Widget(
				$container['multilingualpress.language_switcher_widget_view']
			);
		};

		$container['multilingualpress.language_switcher_widget_view'] = function ( Container $container ) {

			return new Sidebar\LanguageSwitcher\WidgetView(
				$container['multilingualpress.asset_manager']
			);
		};

		$container['multilingualpress.translation_completed_setting_nonce'] = function () {

			return new WPNonce( 'save_translation_completed_setting' );
		};

		$container['multilingualpress.translation_completed_setting_view'] = function ( Container $container ) {

			return new Dashboard\UntranslatedPosts\TranslationCompletedSettingView(
				$container['multilingualpress.untranslated_posts_repository'],
				$container['multilingualpress.translation_completed_setting_nonce']
			);
		};

		$container->share( 'multilingualpress.untranslated_posts_repository', function () {

			return new Dashboard\UntranslatedPosts\TypeSafePostRepository();
		} );

		$container['multilingualpress.untranslated_posts_dashboard_widget'] = function ( Container $container ) {

			/**
			 * Filters the capability required to view the dashboard widget.
			 *
			 * @since 3.0.0
			 *
			 * @param string $capability Capability required to view the dashboard widget.
			 */
			$capability = (string) apply_filters(
				'multilingualpress.untranslated_posts_dashboard_widget_capability',
				'edit_others_posts'
			);

			return new Dashboard\DashboardWidget(
				'multilingualpress-untranslated-posts-dashboard-widget',
				__( 'Untranslated Posts', 'multilingual-press' ),
				$container['multilingualpress.untranslated_posts_dashboard_widget_view'],
				$capability
			);
		};

		$container['multilingualpress.untranslated_posts_dashboard_widget_view'] = function ( Container $container ) {

			return new Dashboard\UntranslatedPosts\WidgetView(
				$container['multilingualpress.site_relations'],
				$container['multilingualpress.untranslated_posts_repository']
			);
		};

		$container['multilingualpress.translation_completed_setting_updater'] = function ( Container $container ) {

			return new Dashboard\UntranslatedPosts\TranslationCompletedSettingUpdater(
				$container['multilingualpress.untranslated_posts_repository'],
				$container['multilingualpress.translation_completed_setting_nonce']
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

		$container['multilingualpress.untranslated_posts_dashboard_widget']->register();

		$container['multilingualpress.language_switcher_widget']->register();

		add_action(
			'post_submitbox_misc_actions',
			[ $container['multilingualpress.translation_completed_setting_view'], 'render' ]
		);

		add_action(
			'save_post',
			[ $container['multilingualpress.translation_completed_setting_updater'], 'update_setting' ],
			10,
			2
		);
	}
}
