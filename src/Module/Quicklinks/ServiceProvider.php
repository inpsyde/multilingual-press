<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Quicklinks;

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\Setting\SettingsBoxView;
use Inpsyde\MultilingualPress\Module\ActivationAwareModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\ActivationAwareness;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\Quicklinks
 * @since   3.0.0
 */
final class ServiceProvider implements ActivationAwareModuleServiceProvider {

	use ActivationAwareness;

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

		$container['multilingualpress.quicklinks'] = function ( Container $container ) {

			return new Quicklinks(
				$container['multilingualpress.translations'],
				$container['multilingualpress.quicklinks_settings_repository'],
				$container['multilingualpress.asset_manager']
			);
		};

		$container['multilingualpress.quicklinks_redirect_hosts_filter'] = function ( Container $container ) {

			return new RedirectHostsFilter( $container['multilingualpress.wpdb'] );
		};

		$container['multilingualpress.quicklinks_redirector'] = function ( Container $container ) {

			return new Redirector( $container['multilingualpress.quicklinks_redirect_hosts_filter'] );
		};

		$container['multilingualpress.quicklinks_settings_box'] = function ( Container $container ) {

			return new QuicklinksSettingsBox(
				$container['multilingualpress.quicklinks_settings_repository'],
				$container['multilingualpress.quicklinks_settings_nonce']
			);
		};

		$container['multilingualpress.quicklinks_settings_nonce'] = function () {

			return new WPNonce( 'update_quicklinks_settings' );
		};

		$container['multilingualpress.quicklinks_settings_repository'] = function () {

			return new TypeSafeSettingsRepository();
		};

		$container['multilingualpress.quicklinks_settings_updater'] = function ( Container $container ) {

			return new SettingsUpdater(
				$container['multilingualpress.quicklinks_settings_repository'],
				$container['multilingualpress.quicklinks_settings_nonce']
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

		$this->on_activation( function () use ( $container ) {

			if ( is_admin() ) {
				$settings_box = $container['multilingualpress.quicklinks_settings_box'];

				add_action( 'multilingualpress.after_module_list', function () use ( $settings_box ) {

					( new SettingsBoxView( $settings_box ) )->render();
				} );

				add_action(
					'multilingualpress.save_modules',
					[ $container['multilingualpress.quicklinks_settings_updater'], 'update_settings' ]
				);
			} else {
				if ( ! empty( $_POST[ Quicklinks::NAME ] ) && is_string( $_POST[ Quicklinks::NAME ] ) ) {
					$container['multilingualpress.quicklinks_redirector']->maybe_redirect(
						$_POST[ Quicklinks::NAME ]
					);
				}

				add_filter(
					'the_content',
					[ $container['multilingualpress.quicklinks'], 'add_to_content' ],
					PHP_INT_MAX
				);
			}
		} );
	}

	/**
	 * Registers the module at the module manager.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 *
	 * @return bool Whether or not the module was registerd successfully AND has been activated.
	 */
	public function register_module( ModuleManager $module_manager ) {

		return $module_manager->register_module( new Module( 'quicklinks', [
			'description' => __( 'Show link to translations in post content.', 'multilingual-press' ),
			'name'        => __( 'Quicklinks', 'multilingual-press' ),
			'active'      => false,
		] ) );
	}
}
