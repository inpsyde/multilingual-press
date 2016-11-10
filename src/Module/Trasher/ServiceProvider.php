<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Trasher;

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Module\ActivationAwareModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\ActivationAwareness;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\Trasher
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

		$container['multilingualpress.trasher'] = function ( Container $container ) {

			return new Trasher(
				$container['multilingualpress.trasher_setting_repository'],
				$container['multilingualpress.content_relations']
			);
		};

		$container['multilingualpress.trasher_setting_nonce'] = function () {

			return new WPNonce( 'save_trasher_setting' );
		};

		$container['multilingualpress.trasher_setting_repository'] = function () {

			return new TypeSafeTrasherSettingRepository();
		};

		$container['multilingualpress.trasher_setting_updater'] = function ( Container $container ) {

			return new TrasherSettingUpdater(
				$container['multilingualpress.trasher_setting_repository'],
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.trasher_setting_nonce']
			);
		};

		$container['multilingualpress.trasher_setting_view'] = function ( Container $container ) {

			return new TrasherSettingView(
				$container['multilingualpress.trasher_setting_repository'],
				$container['multilingualpress.trasher_setting_nonce']
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

		return $module_manager->register_module( new Module( 'trasher', [
			'description' => __(
				'This module provides a new post meta and checkbox to trash the posts. If you enable the checkbox and move a post to the trash MultilingualPress also will trash the linked posts.',
				'multilingual-press'
			),
			'name'        => __( 'Trasher', 'multilingual-press' ),
			'active'      => false,
		] ) );
	}
}
