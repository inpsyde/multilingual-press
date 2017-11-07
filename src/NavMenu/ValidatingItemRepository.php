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
	 * @var int[]
	 */
	private $site_ids = [];

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Request $request HTTP request object.
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

		$menu_id = (int) $this->request->body_value( 'menu', INPUT_GET, FILTER_SANITIZE_NUMBER_INT );
		if ( ! $menu_id ) {
			return [];
		}

		$language_names = get_available_language_names();

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
	 * Returns the site ID for the nav menu item with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $item_id Nav menu item ID.
	 *
	 * @return int Site ID.
	 */
	public function get_site_id( int $item_id ): int {

		if ( isset( $this->site_ids[ $item_id ] ) ) {
			return $this->site_ids[ $item_id ];
		}

		$site_id = (int) get_post_meta( $item_id, ItemRepository::META_KEY_SITE_ID, true );

		$this->site_ids[ $item_id ] = $site_id;

		return $site_id;
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
			'menu-item-title' => esc_attr( $language_name ),
			'menu-item-url'   => get_home_url( $site_id, '/' ),
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

		$item->url = get_home_url( $site_id, '/' );

		if ( empty( $item->classes ) || ! is_array( $item->classes ) ) {
			$item->classes = [];
		}
		$item->classes = array_filter( $item->classes );

		$item->classes[] = "site-id-{$site_id}";
		$item->classes[] = 'mlp-language-nav-item';

		$item->xfn = 'alternate';

		update_post_meta( $item->ID, ItemRepository::META_KEY_SITE_ID, $site_id );

		$item = wp_setup_nav_menu_item( $item );

		return $item;
	}
}
