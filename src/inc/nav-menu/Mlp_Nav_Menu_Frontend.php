<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\Translations;
use Inpsyde\MultilingualPress\Common\Type\Translation;

/**
 * Filters nav menu items and passes the proper URL.
 */
class Mlp_Nav_Menu_Frontend {

	/**
	 * @var string
	 */
	private $meta_key;

	/**
	 * @var int[]
	 */
	private $site_ids = [];

	/**
	 * @var Translations
	 */
	private $translations;

	/**
	 * Constructor.
	 *
	 * @param string       $meta_key         The site ID meta key.
	 * @param Translations $translations Translations API object.
	 */
	public function __construct( $meta_key, Translations $translations ) {

		$this->meta_key = $meta_key;

		$this->translations = $translations;
	}

	/**
	 * Filters the nav menu items.
	 *
	 * @wp-hook wp_nav_menu_objects
	 *
	 * @param WP_Post[] $items Nav menu items.
	 *
	 * @return WP_Post[]
	 */
	public function filter_items( array $items ) {

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
	 * Checks if the site with the given post's remote site ID still exists, and deletes the post if not.
	 *
	 * @param WP_Post $item Nav menu item.
	 *
	 * @return bool
	 */
	public function maybe_delete_obsolete_item( WP_Post $item ) {

		$site_id = $this->get_site_id( $item );
		if ( ! $site_id ) {
			return false;
		}

		if ( \Inpsyde\MultilingualPress\site_exists( $site_id ) ) {
			return false;
		}

		// TODO: Check the object type (could also be a term), and delete the real thing (in a separate method).
		wp_delete_post( $item->ID );

		return true;
	}

	/**
	 * Assigns the remote URL and fires an action hook.
	 *
	 * @param WP_Post       $item         Nav menu item object.
	 * @param Translation[] $translations Translation objects.
	 *
	 * @return void
	 */
	private function prepare_item( WP_Post $item, array $translations ) {

		$site_id = $this->get_site_id( $item );
		if ( ! $site_id ) {
			return;
		}

		if ( get_current_blog_id() === $site_id ) {
			$item->classes[] = 'mlp-current-language-item';
		}

		list( $url, $translation ) = $this->get_item_details( $translations, $site_id );

		/** This filter is documented in inc/types/Mlp_Translation.php */
		$item->url = apply_filters( 'mlp_linked_element_link', $url, $site_id, 0, $translation );

		/**
		 * Runs before a nav menu item is sent to the walker.
		 *
		 * @param WP_Post     $item        Nav menu item object.
		 * @param Translation $translation Translation object.
		 */
		do_action( 'mlp_prepare_nav_menu_item_output', $item, $translation );
	}

	/**
	 * Returns the remote URL and the translation object for the according item.
	 *
	 * @param Translation[] $translations Translation objects.
	 * @param int                         $site_id      Site ID.
	 *
	 * @return array
	 */
	private function get_item_details( array $translations, $site_id ) {

		$home_url = get_home_url( $site_id, '/' );

		if ( empty( $translations[ $site_id ] ) ) {
			return [ $home_url, null ];
		}

		$translation = $translations[ $site_id ];

		$url = $translation->remote_url();
		if ( empty( $url ) ) {
			$url = $home_url;
		}

		return [ $url, $translation ];
	}

	/**
	 * Returns the site ID for the given nav menu item object.
	 *
	 * @param WP_Post $item Nav menu item object.
	 *
	 * @return int
	 */
	private function get_site_id( WP_Post $item ) {

		// TODO: Refactor to use a real cache.
		if ( isset( $this->site_ids[ $item->ID ] ) ) {
			return $this->site_ids[ $item->ID ];
		}

		$site_id = in_array( $item->type, [ 'language', 'custom' ], true )
			? (int) get_post_meta( $item->ID, $this->meta_key, true )
			: 0;

		$this->site_ids[ $item->ID ] = $site_id;

		return $site_id;
	}
}
