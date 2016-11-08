<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\CustomPostTypeSupport;

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
 * @package Inpsyde\MultilingualPress\Module\CustomPostTypeSupport
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

		$container['multilingualpress.post_type_repository'] = function () {

			return new TypeSafePostTypeRepository();
		};

		$container['multilingualpress.post_type_link_url_filter'] = function ( Container $container ) {

			return new URLFilter( $container['multilingualpress.post_type_repository'] );
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

			$nonce = new WPNonce( 'update_custom_post_type_support_settings' );

			$post_type_repository = $container['multilingualpress.post_type_repository'];

			add_action( 'mlp_modules_add_fields', function () use ( $post_type_repository, $nonce ) {

				$post_types = $post_type_repository->get_custom_post_types();
				if ( $post_types ) {
					( new SettingsBoxView( new CustomPostTypeSupportSettingsBox( $post_types, $nonce ) ) )->render();
				}
			} );

			add_action( 'mlp_modules_save_fields', function () use ( $post_type_repository, $nonce ) {

				( new PostTypeSupportSettingsUpdater( $nonce, $post_type_repository ) )->update_settings();
			} );

			add_filter( 'mlp_allowed_post_types', function ( array $post_types ) use ( $post_type_repository ) {

				return array_merge( $post_types, $post_type_repository->get_supported_post_types() );
			} );

			$url_filter = $container['multilingualpress.post_type_link_url_filter'];
			add_action( 'mlp_before_link', [ $url_filter, 'enable' ] );
			add_action( 'mlp_after_link', [ $url_filter, 'disable' ] );
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

		return $module_manager->register_module( new Module( 'custom_post_type_support', [
			'description' => __(
				'Enable translation of custom post types. Creates a second settings box below this. The post types must be activated for the whole network or on the main site.',
				'multilingual-press'
			),
			'name'        => __( 'Custom Post Type Support', 'multilingual-press' ),
			'active'      => false,
		] ) );
	}
}