<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\NavMenu;

use Inpsyde\MultilingualPress\API\Translations;
use Inpsyde\MultilingualPress\Common\Type\NullTranslation;
use Inpsyde\MultilingualPress\Common\Type\Translation;

use function Inpsyde\MultilingualPress\site_exists;

/**
 * Filters nav menu items and passes the proper URL.
 *
 * @package Inpsyde\MultilingualPress\NavMenu
 * @since   3.0.0
 */
class ItemFilter {

	/**
	 * @var int[]
	 */
	private $site_ids = [];

	/**
	 * @var Translations
	 */
	private $translations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Translations $translations Translations API object.
	 */
	public function __construct( Translations $translations ) {

		$this->translations = $translations;
	}

	/**
	 * Filters the nav menu items.
	 *
	 * @since   3.0.0
	 * @wp-hook wp_nav_menu_objects
	 *
	 * @param \WP_Post[] $items Nav menu items.
	 *
	 * @return \WP_Post[] Filtered nav menu items.
	 */
	public function filter_items( array $items ): array {

		$translations = $this->translations->get_translations( [
			'strict'       => false,
			'include_base' => true,
		] );

		foreach ( $items as $key => $item ) {
			if ( $this->maybe_delete_obsolete_item( $item ) ) {
				unset( $items[ $key ] );

				continue;
			}

			if ( $translations ) {
				$this->prepare_item( $item, $translations );
			}
		}

		return $items;
	}

	/**
	 * Checks if the site with the given item's remote site ID still exists, and deletes the item if not.
	 *
	 * @param \WP_Post $item Nav menu item.
	 *
	 * @return bool Whether or not the item was deleted.
	 */
	private function maybe_delete_obsolete_item( \WP_Post $item ): bool {

		$site_id = $this->get_site_id( $item );
		if ( ! $site_id ) {
			return false;
		}

		if ( site_exists( $site_id ) ) {
			return false;
		}

		wp_delete_post( $item->ID );

		return true;
	}

	/**
	 * Assigns the remote URL and fires an action hook.
	 *
	 * @param \WP_Post      $item         Nav menu item object.
	 * @param Translation[] $translations Translation objects.
	 *
	 * @return bool Whether or not the item was prepared successfully.
	 */
	private function prepare_item( \WP_Post $item, array $translations ): bool {

		$site_id = $this->get_site_id( $item );
		if ( ! $site_id ) {
			return false;
		}

		if ( ! isset( $item->classes ) ) {
			$item->classes = [];
		}

		if ( get_current_blog_id() === $site_id ) {
			$item->classes[] = 'mlp-current-language-item';
		}

		list( $url, $translation ) = $this->get_item_details( $translations, $site_id );

		/** This filter is documented in Common\Type\FilterableTranslation.php */
		$item->url = apply_filters( Translation::FILTER_URL, $url, $site_id, 0, $translation );

		/**
		 * Fires right before a nav menu item is sent to the walker.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Post    $item        Nav menu item object.
		 * @param Translation $translation Translation object.
		 */
		do_action( 'multilingualpress.prepare_nav_menu_item', $item, $translation );

		return true;
	}

	/**
	 * Returns the remote URL and the translation object for the according item.
	 *
	 * @param Translation[] $translations Translation objects.
	 * @param int           $site_id      Site ID.
	 *
	 * @return array The remote URL and the translation object for the according item.
	 */
	private function get_item_details( array $translations, int $site_id ): array {

		if ( empty( $translations[ $site_id ] ) ) {
			return [
				get_home_url( $site_id, '/' ),
				new NullTranslation(),
			];
		}

		$translation = $translations[ $site_id ];

		return [
			$translation->remote_url() ?: get_home_url( $site_id, '/' ),
			$translation,
		];
	}

	/**
	 * Returns the site ID for the given nav menu item object.
	 *
	 * @param \WP_Post $item Nav menu item object.
	 *
	 * @return int Site ID.
	 */
	private function get_site_id( \WP_Post $item ): int {

		$item_id = (int) $item->ID;

		if ( isset( $this->site_ids[ $item_id ] ) ) {
			return $this->site_ids[ $item_id ];
		}

		$site_id = in_array( $item->type, [ 'language', 'custom' ], true )
			? (int) get_post_meta( $item->ID, ItemRepository::META_KEY_SITE_ID, true )
			: 0;

		$this->site_ids[ $item_id ] = $site_id;

		return $site_id;
	}
}
