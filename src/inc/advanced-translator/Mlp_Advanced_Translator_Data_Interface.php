<?php
/**
 *
 * @author  toscho
 * @version 2014.03.17
 * @license MIT
 */

interface Mlp_Advanced_Translator_Data_Interface {

	/**
	 * @param WP_Post $post
	 * @param int     $blog_id
	 * @return array
	 */
	public function get_taxonomies( WP_Post $post, $blog_id );

	/**
	 * Base string for name attribute in translation view.
	 *
	 * @return string
	 */
	public function get_name_base();

	/**
	 * Base string for ID attribute in translation view.
	 *
	 * @return string
	 */
	public function get_id_base();
}
