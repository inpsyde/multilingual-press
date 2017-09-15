<?php # -*- coding: utf-8 -*-
/**
 * Simple property object.
 *
 * Note, the magic methods are not called in all contexts, and they
 * cannot return $this.
 *
* @link http://php.net/manual/en/language.oop5.overloading.php
 *
 * Inspired by Steve Yegge.
 * @link {http://steve-yegge.blogspot.de/2008/10/universal-design-pattern.html}
 *
 *
 * There is no __construct(), no __invoke(), no __call() and no __callStatic().
 * You are free to implement those and other methods in a child class.
 *
 * Usage:
 *
 * $properties = new Inpsyde_Property_List;
 * $john->set( 'first_name', 'John' )
 *      ->set( 'last_name', 'Doe' )
 *      ->set( 'phone', 123456789 )
 *      ->freeze(); // no further changes.
 *
 * // John's daughter with the same last name.
 * $mildred = new Inpsyde_Property_List;
 * $mildred->set_parent( $john )
 *         ->set( 'first_name', 'Mildred' );
 *
 *
 *
 * @version    2014.07.14
 * @author     toscho
 * @package    Inpsyde Theme Base
 *
 *
 * @property Mlp_Site_Relations site_relations
 * @property string             plugin_name
 * @property Inpsyde_Autoload   loader
 * @property string             plugin_dir_path
 * @property string             plugin_file_path
 * @property string             plugin_base_name
 * @property string             plugin_url
 * @property string             css_url
 * @property string             js_url
 * @property string             image_url
 * @property string             version
 */
class Inpsyde_Property_List implements Inpsyde_Property_List_Interface {
	/**
	 * List of properties.
	 *
	 * @type array
	 */
	protected $properties = array();

	/**
	 * Parent object.
	 *
	 * Used if a name is not available in this instance.
	 *
	 * @type Inpsyde_Property_List
	 */
	protected $parent = null;

	/**
	 * Record of deleted properties.
	 *
	 * Prevents access to the parent object's properties after deletion
	 * in this instance.
	 *
	 * @see  get()
	 * @type array
	 */
	protected $deleted = array();

	/**
	 * Write and delete protection.
	 *
	 * @see  freeze()
	 * @see  is_frozen()
	 * @type bool
	 */
	protected $frozen = false;

	/**
	 * Set new value.
	 *
	 * @param  string $name
	 * @param  mixed $value
	 * @return void|Inpsyde_Property_List
	 */
	public function set( $name, $value ) {
		if ( $this->frozen ) {
			return $this->stop(
				'This object has been frozen.
				You cannot set properties anymore.'
			);
		}

		$this->properties[ $name ] = $value;
		unset( $this->deleted[ $name ] );

		return $this;
	}

	/**
	 * Import an array or an object as properties.
	 *
	 * @param  array|object $var
	 * @return void|Inpsyde_Property_List
	 */
	public function import( $var ) {
		if ( $this->frozen ) {
			return $this->stop(
				'This object has been frozen.
				You cannot set properties anymore.'
			);
		}

		if ( ! is_array( $var ) && ! is_object( $var ) ) {
			return $this->stop(
				'Cannot import this variable.
				Use arrays and objects only, not a "' . gettype( $var ) . '".'
			);
		}

		foreach ( $var as $name => $value ) {
			$this->properties[ $name ] = $value;
		}

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

		if ( isset( $this->properties[ $name ] ) ) {
			return $this->properties[ $name ];
		}

		if ( isset( $this->deleted[ $name ] ) ) {
			return null;
		}

		if ( null === $this->parent ) {
			return null;
		}

		return $this->parent->get( $name );
	}

	/**
	 * Get all properties.
	 *
	 * @param  boolean $use_parent Get parent object's properties too.
	 * @return array
	 */
	public function get_all( $use_parent = false ) {
		if ( ! $use_parent ) {
			return $this->properties;
		}

		$parent_properties = $this->parent->get_all( true );
		$all               = array_merge( $parent_properties, $this->properties );

		// Strip out properties existing in the parent but deleted in this
		// instance.
		return array_diff( $all, $this->deleted );
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

		if ( isset( $this->properties[ $name ] ) ) {
			return true;
		}

		if ( isset( $this->deleted[ $name ] ) ) {
			return false;
		}

		if ( null === $this->parent ) {
			return false;
		}

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

		if ( $this->frozen ) {
			return $this->stop(
				'This object has been frozen.
				You cannot delete properties anymore.'
			);
		}

		$this->deleted[ $name ] = true;
		unset( $this->properties[ $name ] );

		return $this;
	}

	/**
	 * Set parent object. Properties of this object will be inherited.
	 *
	 * @param  Inpsyde_Property_List_Interface $object
	 * @return Inpsyde_Property_List
	 */
	public function set_parent( Inpsyde_Property_List_Interface $object ) {

		if ( $this->frozen ) {
			return $this->stop(
				'This object has been frozen.
				You cannot change the parent anymore.'
			);
		}

		$this->parent = $object;

		return $this;
	}

	/**
	 * Test if the current instance has a parent.
	 *
	 * @return boolean
	 */
	public function has_parent() {

		return null !== $this->parent;
	}

	/**
	 * Lock write access to this object's instance. Forever.
	 *
	 * @return Inpsyde_Property_List_Interface
	 */
	public function freeze() {

		$this->frozen = true;

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
	 * Used for attempts to write to a frozen instance.
	 *
	 * Might be replaced by a child class.
	 *
	 * @param  string $msg  Error message. Always be specific.
	 * @param  string $code Re-use the same code to group error messages.
	 * @throws Exception
	 * @return void|WP_Error
	 */
	protected function stop( $msg, $code = '' ) {

		if ( '' === $code ) {
			$code = __CLASS__;
		}

		if ( class_exists( 'WP_Error' ) ) {
			return Mlp_WP_Error_Factory::create( $code, $msg );
		}

		throw new Exception( $msg, $code );
	}

	# ==== Magic functions ==== #

	/**
	 * Wrapper for set().
	*
	* @see    set()
	* @param  string $name
	* @param  mixed  $value
	* @return Inpsyde_Property_List_Interface
	*/
	public function __set( $name, $value ) {

		return $this->set( $name,  $value );
	}

	/**
	 * Wrapper for get()
	 *
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
}
