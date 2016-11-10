<?php # -*- coding: utf-8 -*-

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

		$container['multilingualpress.translation_completed_setting_nonce'] = function () {

			return new WPNonce( 'save_translation_completed_setting' );
		};

		$container['multilingualpress.translation_completed_setting_view'] = function ( Container $container ) {

			return new Dashboard\UntranslatedPosts\TranslationCompletedSettingView(
				$container['multilingualpress.untranslated_posts_repository'],
				$container['multilingualpress.translation_completed_setting_nonce']
			);
		};

		$container['multilingualpress.untranslated_posts_repository'] = function () {

			return new Dashboard\UntranslatedPosts\TypeSafePostRepository();
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

		add_action( 'widgets_init', function () {

			/* TODO: With WordPress 4.6 + 2, do the following (via Container?):
			register_widget( new \Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher\Widget(
				new \Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher\WidgetView(),
				$container->get( 'multilingualpress.asset_manager' )
			) );
			*/

			register_widget( '\Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher\Widget' );
		} );
	}
}
