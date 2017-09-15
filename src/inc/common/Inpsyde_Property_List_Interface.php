<?php # -*- coding: utf-8 -*-
/**
 * Simple property object interface.
*
* @version    2014.07.14
* @author     toscho
* @package    Inpsyde
*/
interface Inpsyde_Property_List_Interface {

	/**
	 * Set new value.
	 *
	 * @param  string $name
	 * @param  mixed $value
	 * @return void|Inpsyde_Property_List
	 */
	public function set( $name, $value );

	/**
	 * Get a value.
	 *
	 * Might be taken from parent object.
	 *
	 * @param  string $name
	 * @return mixed
	 */
	public function get( $name );

	/**
	 * Check if property exists.
	 *
	 * Due to syntax restrictions in PHP we cannot name this "isset()".
	 *
	 * @param  string $name
	 * @return boolean
	 */
	public function has( $name );

	/**
	 * Delete a key and set its name to the $deleted list.
	 *
	 * Further calls to has() and get() will not take this property into account.
	 *
	 * @param  string $name
	 * @return void|Inpsyde_Property_List
	 */
	public function delete( $name );

	/**
	 * Set parent object. Properties of this object will be inherited.
	 *
	 * @param  Inpsyde_Property_List_Interface $object
	 * @return Inpsyde_Property_List_Interface
	 */
	public function set_parent( Inpsyde_Property_List_Interface $object );

	/**
	 * Test if the current instance has a parent.
	 *
	 * @return boolean
	 */
	public function has_parent();

	/**
	 * Lock write access to this object's instance. Forever.
	 *
	 * @return Inpsyde_Property_List_Interface $this
	 */
	public function freeze();

	/**
	 * Test from outside if an object has been frozen.
	 *
	 * @return boolean
	 */
	public function is_frozen();

	/**
	 * Wrapper for set().
	 *
	 * @see    set()
	 * @param  string $name
	 * @param  mixed  $value
	 */
	public function __set( $name, $value );

	/**
	 * Wrapper for get()
	 *
	 * @see    get()
	 * @param  string $name
	 * @return mixed
	 */
	public function __get( $name );

	/**
	 * Wrapper for has().
	 *
	 * @see    has()
	 * @param  string $name
	 * @return boolean
	 */
	public function __isset( $name );
}
