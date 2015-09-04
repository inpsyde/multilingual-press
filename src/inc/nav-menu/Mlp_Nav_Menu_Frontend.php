<?php
/**
 * Filters nav menu items and passes the proper URL.
 *
 * @version 2014.09.25
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Nav_Menu_Frontend {

	/**
	 * @var string
	 */
	private $meta_key;

	/**
	 * @type Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * Constructor.
	 *
	 * @param string                     $meta_key
	 * @param Mlp_Language_Api_Interface $language_api
	 */
	public function __construct(
		$meta_key,
		Mlp_Language_Api_Interface $language_api
	) {
		$this->meta_key     = $meta_key;
		$this->language_api = $language_api;
	}

	/**
	 * Filter the nav menu items.
	 *
	 * @wp-hook wp_nav_menu_objects
	 * @param   array $items
	 * @return  array
	 */
	public function filter_items( Array $items ) {

		$args = array (
			'strict'       => FALSE,
			'include_base' => TRUE
		);
		$translations = $this->language_api->get_translations( $args );

		if ( empty ( $translations ) )
			return $items;

		foreach ( $items as $item )
			$this->prepare_item( $item, $translations );

		return $items;
	}

	/**
	 * Assign remote URL and call hooks
	 *
	 * @param WP_Post $item
	 * @param array   $translations
	 * @return void
	 */
	private function prepare_item( WP_Post $item, Array $translations ) {

		$site_id = $this->get_site_id( $item );

		if ( ! $site_id )
			return;

		list ( $url, $translation ) = $this->get_item_details( $translations, $site_id );

		/** This filter is documented in inc/types/Mlp_Translation.php */
		$item->url = apply_filters( 'mlp_linked_element_link', $url, $site_id, 0, $translation );

		/**
		 * Runs before a nav menu item is sent to the walker.
		 *
		 * @param WP_Post                   $item        Nav menu item object.
		 * @param Mlp_Translation_Interface $translation Translation object.
		 */
		do_action( 'mlp_prepare_nav_menu_item_output', $item, $translation );
	}

	/**
	 * Find the translation object and the URL for an item
	 *
	 * @param array $translations
	 * @param int   $site_id
	 * @return array
	 */
	private function get_item_details( Array $translations, $site_id ) {

		$site_url = get_site_url( $site_id, '/' );

		if ( empty ( $translations[ $site_id ] ) )
			return array ( $site_url, NULL );

		/** @var Mlp_Translation_Interface $translation */
		$translation = $translations[ $site_id ];
		$url         = $translation->get_remote_url();

		if ( empty ( $url ) )
			$url = $site_url;

		return array ( $url, $translation );
	}

	/**
	 * @param WP_Post $item
	 * @return int
	 */
	private function get_site_id( WP_Post $item ) {

		if ( ! in_array( $item->type, array( 'language', 'custom' ) ) )
			return 0;

		return (int) get_post_meta( $item->ID, $this->meta_key, TRUE );
	}
}
