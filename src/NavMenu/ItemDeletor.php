<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\NavMenu;

use wpdb;

/**
 * Deletes nav menu items.
 *
 * @package Inpsyde\MultilingualPress\NavMenu
 * @since   3.0.0
 */
class ItemDeletor {

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param wpdb $db Database object.
	 */
	public function __construct( wpdb $db ) {

		$this->db = $db;
	}

	/**
	 * Deletes all remote MultilingualPress nav menu items linking to the (to-be-deleted) site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $deleted_site_id The ID of the to-be-deleted site.
	 *
	 * @return int Number of deleted nav items.
	 */
	public function delete_items_for_deleted_site( $deleted_site_id ): int {

		$deleted = 0;

		$query = "
SELECT blog_id
FROM {$this->db->blogs}
WHERE blog_id != %d";
		$query = $this->db->prepare( $query, $deleted_site_id );

		foreach ( $this->db->get_col( $query ) as $site_id ) {
			switch_to_blog( $site_id );

			$query = "
SELECT p.ID
FROM {$this->db->posts} p
INNER JOIN {$this->db->postmeta} pm
ON p.ID = pm.post_id
WHERE pm.meta_key = %s
	AND pm.meta_value = %s";
			$query = $this->db->prepare( $query, ItemRepository::META_KEY_SITE_ID, $deleted_site_id );

			foreach ( $this->db->get_col( $query ) as $post_id ) {
				if ( wp_delete_post( $post_id, true ) ) {
					$deleted++;
				}
			}

			restore_current_blog();
		}

		return $deleted;
	}
}
