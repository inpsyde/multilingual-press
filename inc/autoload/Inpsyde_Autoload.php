<?php # -*- coding: utf-8 -*-
/**
 * Collect auto-load rules and register a common auto-load callback.
 *
 * These autoload files are used in multiple projects,
 * hence the different package name.
 *
 * @author     toscho
 * @since      2013.08.18
 * @version    2013.08.22
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package    Inpsyde
 * @subpackage Autoload
 */
class Inpsyde_Autoload
{
	private $rules = array ();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		spl_autoload_register( array ( $this, 'load' ) );
	}

	/**
	 * Add a rule as object instance.
	 *
	 * @param  Inpsyde_Suite_Autoload_Rule $rule
	 * @return Inpsyde_Autoload
	 */
	public function add_rule( Inpsyde_Autoload_Rule_Interface $rule )
	{
		$this->rules[] = $rule;
		return $this;
	}

	/**
	 * Callback for spl_autoload_register()
	 *
	 * @param  string  $class_name
	 * @return boolean
	 */
	public function load( $name )
	{
		foreach ( $this->rules as $rule )
			if ( $rule->load( $name ) )
				return;
	}
}