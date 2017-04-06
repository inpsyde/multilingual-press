<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\NavMenu;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
use function Inpsyde\MultilingualPress\get_available_language_names;
use function Inpsyde\MultilingualPress\site_exists;

/**
 * Item repository implementation validating sites and menu.
 *
 * @package Inpsyde\MultilingualPress\NavMenu
 * @since   3.0.0
 */
final class ValidatingItemRepository implements ItemRepository {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param Request $request
	 */
	public function __construct( Request $request ) {

		$this->request = $request;
	}

	/**
	 * Returns the according items for the sites with the given IDs.
	 *
	 * @since 3.0.0
	 *
	 * @param int[] $site_ids Site IDs.
	 *
	 * @return object[] The items for the sites with the given IDs.
	 */
	public function get_items_for_sites( array $site_ids ): array {

		if ( ! $site_ids ) {
			return [];
		}

		$menu = $this->request->body_value( 'menu', INPUT_GET, FILTER_SANITIZE_NUMBER_INT );

		if ( $menu === null ) {
			return [];
		}

		$language_names = get_available_language_names();

		$menu_id = (int) $menu;

		$items = [];

		foreach ( $site_ids as $site_id ) {

			$site_id = (int) $site_id;

			if ( empty( $language_names[ $site_id ] ) || ! site_exists( $site_id ) ) {
				continue;
			}

			$item = $this->ensure_item( $menu_id, $site_id, $language_names[ $site_id ] );
			if ( $item instanceof \WP_Post ) {
				$items[] = $this->prepare_item( $item, $site_id );
			}
		}

		return $items;
	}

	/**
	 * Ensures that an item according to the given arguments exists in the database.
	 *
	 * @param int    $menu_id       Menu ID.
	 * @param int    $site_id       Site ID.
	 * @param string $language_name Language name.
	 *
	 * @return \WP_Post|null Post object on success, and null on failure.
	 */
	private function ensure_item( int $menu_id, int $site_id, string $language_name ) {

		return get_post( wp_update_nav_menu_item( $menu_id, 0, [
			'menu-item-title'      => esc_attr( $language_name ),
			'menu-item-type'       => 'language',
			'menu-item-object'     => 'custom',
			'menu-item-url'        => get_home_url( $site_id, '/' ),
			'menu_item-type-label' => esc_html__( 'Language', 'multilingualpress' ),
		] ) );
	}

	/**
	 * Prepares the given item for use.
	 *
	 * @param \WP_Post $item    Menu item object.
	 * @param int      $site_id Site ID.
	 *
	 * @return object Menu item object.
	 */
	private function prepare_item( \WP_Post $item, int $site_id ) {

		$item->object = 'mlp_language';

		$item->post_type = 'nav_menu_item';

		$item->xfn = 'alternate';

		$item = wp_setup_nav_menu_item( $item );

		$item->label = $item->title;

		$item->type_label = esc_html__( 'Language', 'multilingualpress' );

		$item->classes[] = "blog-id-{$site_id}";
		$item->classes[] = 'mlp-language-nav-item';

		$home_url = get_home_url( $site_id, '/' );

		$item->url = $home_url;

		update_post_meta( $item->ID, ItemRepository::META_KEY_SITE_ID, $site_id );

		update_post_meta( $item->ID, '_menu_item_url', esc_url_raw( $home_url ) );

		return $item;
	}
}
