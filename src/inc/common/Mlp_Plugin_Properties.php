<?php
/**
 * Holds data about the plugin MultilingualPress
 *
 * @version 2014.10.06
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */


class Mlp_Plugin_Properties implements Inpsyde_Property_List_Interface {

	/**
	 * List of properties.
	 *
	 * @type array
	 */
	private $properties = array ();

	/**
	 * Parent object.
	 *
	 * Used if a name is not available in this instance.
	 *
	 * @type Inpsyde_Property_List
	 */
	private $parent = NULL;

	/**
	 * Record of deleted properties.
	 *
	 * Prevents access to the parent object's properties after deletion
	 * in this instance.
	 *
	 * @see  get()
	 * @type array
	 */
	private $deleted = array ();

	/**
	 * Write and delete protection.
	 *
	 * @see  freeze()
	 * @see  is_frozen()
	 * @type bool
	 */
	private $frozen = FALSE;

	/**
	 * @type Mlp_Internal_Locations
	 */
	private $locations;

	/**
	 * Set new value.
	 *
	 * @param  string $name
	 * @param  mixed  $value
	 * @return void|Mlp_Plugin_Properties
	 */
	public function set( $name, $value ) {
		if ( $this->frozen )
			return $this->stop(
						'This object has been frozen.
						You cannot set properties anymore.'
			);

		if ( 'locations' === $name )
			$this->locations = $value;
		else
			$this->properties[ $name ] = $value;

		unset ( $this->deleted[ $name ] );

		return $this;
	}

	/**
	 * Get a value.
	 *
	 * Might be taken from parent object.
	 *
	 * @param  string $name
	 * @return mixed
	 */
	public function get( $name ) {

		if ( 'locations' === $name )
			return $this->locations;

		if ( 'css_url' === $name )
			return $this->locations->get_dir( 'css', 'url' );

		if ( 'js_url' === $name )
			return $this->locations->get_dir( 'js', 'url' );

		if ( 'flag_url' === $name )
			return $this->locations->get_dir( 'flags', 'url' );

		if ( 'flag_path' === $name )
			return $this->locations->get_dir( 'flags', 'path' );

		if ( 'image_url' === $name )
			return $this->locations->get_dir( 'images', 'url' );

		if ( 'plugin_dir_path' === $name )
			return $this->locations->get_dir( 'plugin', 'path' );

		if ( 'plugin_url' === $name )
			return $this->locations->get_dir( 'plugin', 'url' );

		if ( isset ( $this->properties[ $name ] ) )
			return $this->properties[ $name ];

		if ( isset ( $this->deleted[ $name ] ) )
			return NULL;

		if ( NULL === $this->parent )
			return NULL;

		return $this->parent->get( $name );
	}

	/**
	 * Check if property exists.
	 *
	 * Due to syntax restrictions in PHP we cannot name this "isset()".
	 *
	 * @param  string $name
	 * @return boolean
	 */
	public function has( $name ) {

		if ( isset ( $this->properties[ $name ] ) )
			return TRUE;

		if ( isset ( $this->deleted[ $name ] ) )
			return FALSE;

		if ( NULL === $this->parent )
			return FALSE;

		return $this->parent->has( $name );
	}

	/**
	 * Delete a key and set its name to the $deleted list.
	 *
	 * Further calls to has() and get() will not take this property into account.
	 *
	 * @param  string $name
	 * @return void|Inpsyde_Property_List
	 */
	public function delete( $name ) {

		if ( $this->frozen )
			return $this->stop(
						'This object has been frozen.
						You cannot delete properties anymore.'
			);

		$this->deleted[ $name ] = TRUE;
		unset ( $this->properties[ $name ] );

		return $this;
	}

	/**
	 * Set parent object. Properties of this object will be inherited.
	 *
	 * @param  Inpsyde_Property_List_Interface $object
	 * @return Inpsyde_Property_List_Interface
	 */
	public function set_parent( Inpsyde_Property_List_Interface $object ) {

		if ( $this->frozen )
			return $this->stop(
						'This object has been frozen.
						You cannot change the parent anymore.'
			);

		$this->parent = $object;

		return $this;
	}

	/**
	 * Test if the current instance has a parent.
	 *
	 * @return boolean
	 */
	public function has_parent() {

		return NULL !== $this->parent;
	}

	/**
	 * Lock write access to this object's instance. Forever.
	 *
	 * @return Inpsyde_Property_List_Interface $this
	 */
	public function freeze() {

		$this->frozen = TRUE;

		return $this;
	}

	/**
	 * Test from outside if an object has been frozen.
	 *
	 * @return boolean
	 */
	public function is_frozen() {

		return $this->frozen;
	}

	/**
	 * Wrapper for set().
	 *
	 * @see    set()
	 * @param  string $name
	 * @param  mixed  $value
	 * @return void|Mlp_Plugin_Properties
	 */
	public function __set( $name, $value ) {

		return $this->set( $name,  $value );
	}

	/**
	 * Wrapper for get()
	 *
	 * @see    get()
	 * @param  string $name
	 * @return mixed
	 */
	public function __get( $name ) {

		return $this->get( $name );
	}

	/**
	 * Wrapper for has().
	 *
	 * @see    has()
	 * @param  string $name
	 * @return boolean
	 */
	public function __isset( $name ) {

		return $this->has( $name );
	}

	/**
	 * Used for attempts to write to a frozen instance.
	 *
	 * Might be replaced by a child class.
	 *
	 * @param  string $msg  Error message. Always be specific.
	 * @param  string $code Re-use the same code to group error messages.
	 * @throws Exception
	 * @return void|WP_Error
	 */
	private function stop( $msg, $code = '' ) {

		if ( '' === $code )
			$code = __CLASS__;

		if ( class_exists( 'WP_Error' ) )
			return Mlp_WP_Error_Factory::create( $code, $msg );

		throw new Exception( $msg, $code );
	}
}
