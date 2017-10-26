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

		$container['multilingualpress.language_meta_labels'] = function () {
			return new LanguageMetaLabels();
		};

		$container['multilingualpress.language_edit_view'] = function( Container $container ) {
			return new LanguageEditView(
				$container['multilingualpress.languages'],
				$container['multilingualpress.language_meta_labels']
			);
		};

		$container['multilingualpress.language_manager_settings_page_view'] = function ( Container $container ) {

			// We cannot pass a WP_List_Table instance here, because the
			// registration runs before the necessary admin files are included.
			return new LanguageManagerSettingsPageView(
				$container['multilingualpress.update_languages_nonce'],
				$container['multilingualpress.asset_manager'],
				$container['multilingualpress.server_request']
			);
		};

		// TODO: Add missing structures such as language updater or repository, table resetter, table reset nonce etc.

		// TODO: Pass this on to the language updater or repository.
		$container['multilingualpress.update_languages_nonce'] = function () {
			return new WPNonce( 'update_languages' );
		};

		$container['multilingualpress.language_usage_list'] = function ( Container $container ) {
			return new LanguageUsageList( $container['multilingualpress.languages'] );
		};

		// the following entry isn't really used, because the Container
		// object doesn't work late enough at the moment.
		$container['multilingualpress.language_list_table'] = function ( Container $container ) {
			$active_languages = $container['multilingualpress.language_usage_list']->get_by( LanguageUsageList::ACTIVE );
			return new LanguageListTable( $active_languages );
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
		$labels = $container['multilingualpress.language_meta_labels'];
		$usage  = $container['multilingualpress.language_usage_list'];

		add_action(
			LanguageManagerSettingsPageView::ACTION_CONTENT_DISPLAY,
			function() use ( $labels, $usage ) {
				$active_languages = $usage->get_by( LanguageUsageList::ACTIVE );
				// I cannot create an instance earlier, because many classes and
				// functions aren't loaded yet when bootstrap() is called.
				$table = new LanguageListTable( $active_languages, $labels );
				$table->setup();
		});

		add_action( LanguageManagerSettingsPageView::ACTION_SINGLE_LANGUAGE_DISPLAY,
            [
	            $container['multilingualpress.language_edit_view'],
	            'render'
            ]
		);
	}
}
