<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\Common\Admin\SettingsPage;
use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Service provider for all language manager objects.
 *
 * @package Inpsyde\MultilingualPress\LanguageManager
 * @since   3.0.0
 */
final class LanguageManagerServiceProvider implements BootstrappableServiceProvider {

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

		$container['multilingualpress.language_manager_settings_page'] = function ( Container $container ) {

			return SettingsPage::with_parent(
				SettingsPage::ADMIN_NETWORK,
				SettingsPage::PARENT_NETWORK_SETTINGS,
				__( 'Language Manager', 'multilingual-press' ),
				__( 'Language Manager', 'multilingual-press' ),
				'manage_network_options',
				'language-manager',
				$container['multilingualpress.language_manager_settings_page_view']
			);
		};

		$container['multilingualpress.language_manager_settings_page_view'] = function ( Container $container ) {

			// TODO: This replaces \Mlp_Language_Manager_Page_View. Adapt to your needs, using $container.
			return new LanguageManagerSettingsPageView(
				$container['multilingualpress.update_languages_nonce'],
				$container['multilingualpress.asset_manager']
			);
		};

		// TODO: Add missing structures such as language updater or repository, table resetter, table reset nonce etc.

		// TODO: Pass this on to the language updater or repository.
		$container['multilingualpress.update_languages_nonce'] = function () {

			return new WPNonce( 'update_languages' );
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

		$container['multilingualpress.language_manager_settings_page']->register();

		// TODO: Refactor the following!
		new \Mlp_Language_Manager_Controller(
			new \Mlp_Language_Db_Access(
				$container['multilingualpress.languages_table']->name()
			),
			$container['multilingualpress.wpdb']
		);
	}
}
