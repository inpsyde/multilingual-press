<?php
/**
 * Interface for meta data.
 *
 * Used by the view.
 *
 * @author  Inpsyde GmbH, toscho
 * @version 2014.03.14
 * @license GPL
 */
interface Mlp_Translatable_Post_Data_Interface {

	/**
	 * @param  WP_Post $source_post
	 * @param  int     $blog_id
	 * @return WP_Post
	 */
	public function get_remote_post( WP_Post $source_post, $blog_id );

	/**
	 * @param  string $post_type
	 * @return WP_Post
	 */
	public function get_dummy_post( $post_type );

	/**
	 * @param  int $blog_id
	 * @return string
	 */
	public function get_remote_language( $blog_id );

	/**
	 * @param  WP_Post $post
	 * @return string
	 */
	public function get_real_post_type( WP_Post $post );

	/**
	 * @param  int $post_id
	 * @return array
	 */
	public function get_post_meta_to_transfer( $post_id );

	/**
	 * set the source id of the element
	 *
	 * @param   int $element_id ID of the selected element
	 * @param   int $source_id ID of the selected element
	 * @param   int $source_blog_id ID of the selected blog
	 * @param   string $type type of the selected element
	 * @param   int $blog_id ID of the selected blog
	 * @global	$wpdb | WordPress Database Wrapper
	 * @return  void
	 */
	public function set_linked_element( $element_id, $source_id = 0, $source_blog_id = 0, $type = '', $blog_id = 0 );

	/**
	 * Add source post meta to remote post.
	 *
	 * @param  int   $remote_post_id
	 * @param  array $post_meta
	 * @return void
	 */
	public function update_remote_post_meta( $remote_post_id, $post_meta = array() );

	/**
	 * Set the source id
	 *
	 * @param   int    $sourceid    ID of the selected element
	 * @param   int    $source_blog ID of the selected blog
	 * @param   string $source_type type of the selected element
	 * @return  void
	 */
	public function set_source_id( $sourceid, $source_blog = 0, $source_type = '' );
}