<?php
/**
 * Handle updates for user settings.
 *
 * @version 2014.07.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_User_Settings_Updater_Interface {

	/**
	 * @param  int $user_id
	 * @return bool
	 */
	public function save( $user_id );
}