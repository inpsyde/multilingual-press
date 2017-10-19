<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\Common\Admin\SettingsPage;
use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Common\Type\Language;

use function Inpsyde\MultilingualPress\get_available_languages;

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

		add_action( LanguageManagerSettingsPageView::CONTENT_DISPLAY, function() use ( $container ) {

			$separator = new LanguageUsageList( $container['multilingualpress.languages'] );

			$table = new LanguageListTable( $separator->get_by( LanguageUsageList::ACTIVE ) );
			$table->prepare_items();
			$table->display();
			return;
		});

		$container['multilingualpress.languagelisttable'] = function ( Container $container ) {

			return new LanguageListTable( $container['multilingualpress.languages'] );
		};

		$container['multilingualpress.language_manager_settings_page_view'] = function ( Container $container ) {

			// We cannot pass a WP_List_Table instance here, because the
			// registration runs before the necessary admin files are included.
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
	}
}
