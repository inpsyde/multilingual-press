<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\NavMenu;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox;
use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Service provider for all nav menu objects.
 *
 * @package Inpsyde\MultilingualPress\NavMenu
 * @since   3.0.0
 */
final class NavMenuServiceProvider implements BootstrappableServiceProvider {

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

		$container['multilingualpress.add_languages_to_nav_menu_nonce'] = function () {

			return new WPNonce( 'add_languages_to_nav_menu' );
		};

		$container['multilingualpress.nav_menu_ajax_handler'] = function ( Container $container ) {

			return new AJAXHandler(
				$container['multilingualpress.add_languages_to_nav_menu_nonce'],
				$container['multilingualpress.nav_menu_item_repository'],
				$container['multilingualpress.server_request']
			);
		};

		$container['multilingualpress.nav_menu_item_deletor'] = function ( Container $container ) {

			return new ItemDeletor(
				$container['multilingualpress.wpdb']
			);
		};

		$container['multilingualpress.nav_menu_item_filter'] = function ( Container $container ) {

			return new ItemFilter(
				$container['multilingualpress.translations'],
				$container['multilingualpress.nav_menu_item_repository']
			);
		};

		$container->share( 'multilingualpress.nav_menu_item_repository', function ( Container $container ) {

			return new ValidatingItemRepository(
				$container['multilingualpress.server_request']
			);
		} );

		$container['multilingualpress.nav_menu_meta_box_model'] = function () {

			return new LanguagesMetaBoxModel();
		};

		$container['multilingualpress.nav_menu_meta_box_view'] = function ( Container $container ) {

			return new LanguagesMetaBoxView(
				$container['multilingualpress.nav_menu_meta_box_model']
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
			'delete_blog',
			[ $container['multilingualpress.nav_menu_item_deletor'], 'delete_items_for_deleted_site' ]
		);

		if ( is_admin() ) {
			$asset_manager = $container['multilingualpress.asset_manager'];

			$meta_box_model = $container['multilingualpress.nav_menu_meta_box_model'];

			$meta_box_view = $container['multilingualpress.nav_menu_meta_box_view'];

			$nonce = $container['multilingualpress.add_languages_to_nav_menu_nonce'];

			add_action( 'load-nav-menus.php', function () use ( $asset_manager, $meta_box_model, $nonce ) {

				$asset_manager->enqueue_script_with_data( 'multilingualpress-admin', 'mlpNavMenusSettings', [
					'action'    => AJAXHandler::ACTION,
					'metaBoxId' => $meta_box_model->id(),
					'nonce'     => (string) $nonce,
					'nonceName' => $nonce->action(),
				] );

				$asset_manager->enqueue_style( 'multilingualpress-admin' );
			} );

			add_action( 'admin_init', function () use ( $meta_box_model, $meta_box_view ) {

				( new MetaBox( $meta_box_model, $meta_box_view ) )->register( 'nav-menus', 'side', 'low' );
			} );

			add_action(
				'wp_ajax_' . AJAXHandler::ACTION,
				[ $container['multilingualpress.nav_menu_ajax_handler'], 'send_items' ]
			);

			$item_repository = $container['multilingualpress.nav_menu_item_repository'];

			add_action( 'wp_setup_nav_menu_item', function ( $item ) use ( $item_repository ) {

				if ( $item_repository->get_site_id( (int) $item->ID ?? 0 ) ) {
					$item->type_label = esc_html__( 'Language', 'multilingualpress' );
				}

				return $item;
			} );

		} else {
			add_filter(
				'wp_nav_menu_objects',
				[ $container['multilingualpress.nav_menu_item_filter'], 'filter_items' ]
			);
		}
	}
}
