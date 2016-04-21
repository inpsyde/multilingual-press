<?php # -*- coding: utf-8 -*-

/**
 * Handles deletion of remote MultilingualPress nav menu items linking to a deleted site.
 */
class Mlp_Nav_Menu_Item_Deletor {

	/**
	 * @var string
	 */
	private $meta_key;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param wpdb   $wpdb     WordPress database wrapper object.
	 * @param string $meta_key Site ID meta key for nav_menu posts.
	 */
	public function __construct( wpdb $wpdb, $meta_key ) {

		$this->wpdb = $wpdb;

		$this->meta_key = $meta_key;
	}

	/**
	 * Deletes all remote MultilingualPress nav menu items linking to the (to-be-deleted) site with the given ID.
	 *
	 * @param int $deleted_site_id The ID of the to-be-deleted site.
	 *
	 * @return void
	 */
	public function delete_items_for_deleted_site( $deleted_site_id ) {

		$query = "
SELECT blog_id
FROM {$this->wpdb->blogs}
WHERE blog_id != %d";
		$query = $this->wpdb->prepare( $query, $deleted_site_id );

		foreach ( $this->wpdb->get_col( $query ) as $site_id ) {
			switch_to_blog( $site_id );

			$query = "
SELECT p.ID
FROM {$this->wpdb->posts} p
INNER JOIN {$this->wpdb->postmeta} pm
ON p.ID = pm.post_id
WHERE pm.meta_key = %s
	AND pm.meta_value = %s";
			$query = $this->wpdb->prepare( $query, $this->meta_key, $deleted_site_id );

			foreach ( $this->wpdb->get_col( $query ) as $post_id ) {
				wp_delete_post( $post_id, true );
			}

			restore_current_blog();
		}
	}
}
