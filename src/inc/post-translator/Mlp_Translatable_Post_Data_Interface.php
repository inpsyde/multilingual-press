<?php

/**
 * Interface for meta data.
 *
 * Used by the view.
 *
 * @author  Inpsyde GmbH, toscho, tf
 * @version 2015.08.21
 * @license GPL
 */
interface Mlp_Translatable_Post_Data_Interface {

	/**
	 * @param WP_Post $source_post
	 * @param int     $blog_id
	 *
	 * @return WP_Post
	 */
	public function get_remote_post( WP_Post $source_post, $blog_id );

	/**
	 * @param string $post_type
	 *
	 * @return WP_Post
	 */
	public function get_dummy_post( $post_type );

	/**
	 * @param int $blog_id
	 *
	 * @return string
	 */
	public function get_remote_language( $blog_id );

	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_real_post_type( WP_Post $post );

	/**
	 * @return array
	 */
	public function get_post_meta_to_transfer();

	/**
	 * set the source id of the element
	 *
	 * @param   int $source_content_id ID of current element
	 * @param   int $remote_site_id    ID of remote site
	 * @param   int $remote_content_id ID of remote content
	 *
	 * @return  void
	 */
	public function set_linked_element( $source_content_id, $remote_site_id, $remote_content_id );

	/**
	 * Add source post meta to remote post.
	 *
	 * @param  int   $remote_post_id
	 * @param  array $post_meta
	 *
	 * @return void
	 */
	public function update_remote_post_meta( $remote_post_id, $post_meta = array() );

	/**
	 * @param string $post_type
	 * @param int    $post_parent
	 *
	 * @return void
	 */
	public function find_post_parents( $post_type, $post_parent );

	/**
	 * @param $blog_id
	 *
	 * @return int
	 */
	public function get_post_parent( $blog_id );

	/**
	 * Figure out the post ID.
	 *
	 * Inspects POST request data and too, because we get two IDs on auto-drafts.
	 *
	 * @param  int $post_id
	 *
	 * @return int
	 */
	public function get_real_post_id( $post_id );

	/**
	 * Set the context of the to-be-saved post.
	 *
	 * @param array $save_context Save context.
	 *
	 * @return void
	 */
	public function set_save_context( array $save_context = array() );

	/**
	 * Check if the current request should be processed by save().
	 *
	 * @param WP_Post $post
	 * @param string  $name_base
	 *
	 * @return bool
	 */
	public function is_valid_save_request( WP_Post $post, $name_base = '' );
}
