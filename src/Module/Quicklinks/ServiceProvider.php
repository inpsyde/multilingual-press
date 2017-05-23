<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Quicklinks;

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\Setting\SettingsBoxView;
use Inpsyde\MultilingualPress\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\Quicklinks
 * @since   3.0.0
 */
final class ServiceProvider implements ModuleServiceProvider {

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

			return new RedirectHostsFilter(
				$container['multilingualpress.wpdb']
			);
		};

		$container['multilingualpress.quicklinks_redirector'] = function ( Container $container ) {

			return new Redirector(
				$container['multilingualpress.quicklinks_redirect_hosts_filter']
			);
		};

		$container['multilingualpress.quicklinks_settings_box'] = function ( Container $container ) {

			return new QuicklinksSettingsBox(
				$container['multilingualpress.quicklinks_settings_repository'],
				$container['multilingualpress.update_quicklinks_settings_nonce']
			);
		};

		$container->share( 'multilingualpress.quicklinks_settings_repository', function () {

			return new TypeSafeSettingsRepository();
		} );

		$container['multilingualpress.quicklinks_settings_updater'] = function ( Container $container ) {

			return new SettingsUpdater(
				$container['multilingualpress.quicklinks_settings_repository'],
				$container['multilingualpress.update_quicklinks_settings_nonce']
			);
		};

		$container['multilingualpress.update_quicklinks_settings_nonce'] = function () {

			return new WPNonce( 'update_quicklinks_settings' );
		};
	}

	/**
	 * Registers the module at the module manager.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 *
	 * @return bool Whether or not the module was registered successfully AND has been activated.
	 */
	public function register_module( ModuleManager $module_manager ): bool {

		return $module_manager->register_module( new Module( 'quicklinks', [
			'description' => __( 'Show link to translations in post content.', 'multilingualpress' ),
			'name'        => __( 'Quicklinks', 'multilingualpress' ),
			'active'      => false,
		] ) );
	}

	/**
	 * Performs various tasks on module activation.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function activate_module( Container $container ) {

		if ( is_admin() ) {
			$this->activate_module_for_admin( $container );

			return;
		}

		$this->activate_module_for_front_end( $container );
	}

	/**
	 * Performs various admin-specific tasks on module activation.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function activate_module_for_admin( Container $container ) {

		$settings_box = $container['multilingualpress.quicklinks_settings_box'];

		add_action( 'multilingualpress.after_module_list', function () use ( $settings_box ) {

			( new SettingsBoxView( $settings_box ) )->render();
		} );

		add_action(
			'multilingualpress.save_modules',
			[ $container['multilingualpress.quicklinks_settings_updater'], 'update_settings' ]
		);
	}

	/**
	 * Performs various admin-specific tasks on module activation.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function activate_module_for_front_end( Container $container ) {

		$name = $container['multilingualpress.server_request']->body_value( Quicklinks::NAME, INPUT_POST );

		if ( $name && is_string( $name ) ) {
			$container['multilingualpress.quicklinks_redirector']->maybe_redirect( $name );
		}

		add_filter(
			'the_content',
			[ $container['multilingualpress.quicklinks'], 'add_to_content' ],
			PHP_INT_MAX
		);
	}
}
