<?php # -*- coding: utf-8 -*-

/**
 * Interface for classes providing a very simple event handling.
 *
 * @version 2015.06.30
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Updatable {

	/**
	 * @param string $name
	 *
	 * @return mixed|void Either a value, or void for actions.
	 */
	public function update( $name );

}
