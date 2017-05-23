<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Trasher;

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\Trasher
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

		$container['multilingualpress.save_trasher_setting_nonce'] = function () {

			return new WPNonce( 'save_trasher_setting' );
		};

		$container['multilingualpress.trasher'] = function ( Container $container ) {

			return new Trasher(
				$container['multilingualpress.trasher_setting_repository'],
				$container['multilingualpress.content_relations']
			);
		};

		$container->share( 'multilingualpress.trasher_setting_repository', function () {

			return new TypeSafeTrasherSettingRepository();
		} );

		$container['multilingualpress.trasher_setting_updater'] = function ( Container $container ) {

			return new TrasherSettingUpdater(
				$container['multilingualpress.trasher_setting_repository'],
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.server_request'],
				$container['multilingualpress.save_trasher_setting_nonce']
			);
		};

		$container['multilingualpress.trasher_setting_view'] = function ( Container $container ) {

			return new TrasherSettingView(
				$container['multilingualpress.trasher_setting_repository'],
				$container['multilingualpress.save_trasher_setting_nonce']
			);
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

		return $module_manager->register_module( new Module( 'trasher', [
			'description' => __(
				'This module provides a new post meta and checkbox to trash the posts. If you enable the checkbox and move a post to the trash MultilingualPress also will trash the linked posts.',
				'multilingualpress'
			),
			'name'        => __( 'Trasher', 'multilingualpress' ),
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

		add_action(
			'post_submitbox_misc_actions',
			[ $container['multilingualpress.trasher_setting_view'], 'render' ]
		);

		add_action(
			'save_post',
			[ $container['multilingualpress.trasher_setting_updater'], 'update_settings' ],
			10,
			2
		);

		add_action( 'wp_trash_post', [ $container['multilingualpress.trasher'], 'trash_related_posts' ] );
	}
}
