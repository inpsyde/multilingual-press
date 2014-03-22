<?php # -*- coding: utf-8 -*-
/**
 * Interface for classes providing a very simple event handling.
 *
 * @version 2014.03.03
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Updatable {
	/**
	 * @param  string $name
	 * @return mixed  Either void for actions or a value.
	 */
	public function update( $name );
}