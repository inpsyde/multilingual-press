<?php
/**
 * Interface Mlp_Save_Post_Interface
 *
 * @version 2014.03.17
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Save_Post_Interface {

	/**
	 * @param int     $post_id
	 * @param WP_Post $post
	 * @return void
	 */
	public function save( $post_id, WP_Post $post );

}