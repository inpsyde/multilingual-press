<?php
/**
 * Show content of user profile setting table row.
 *
 * @version 2014.07.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_User_Settings_View_Interface {

	/**
	 * Content of 'th'.
	 *
	 * @param WP_User $user
	 * @return void
	 */
	public function show_header( WP_User $user );

	/**
	 * Content of 'td'.
	 *
	 * @param WP_User $user
	 * @return void
	 */
	public function show_content( WP_User $user );
}