<?php # -*- coding: utf-8 -*-
/**
 * Collect auto-load rules and register a common auto-load callback.
 *
 * These autoload files are used in multiple projects,
 * hence the different package name.
 *
 * @author     toscho
 * @since      2013.08.18
 * @version    2014.03.26
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package    Inpsyde
 * @subpackage Autoload
 */
class Inpsyde_Autoload
{
	/**
	 * List of auto-load rules
	 *
	 * @var array
	 */
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
	 * @param  Inpsyde_Autoload_Rule_Interface $rule
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
	 * @param  string  $name
	 */
	public function load( $name )
	{
		/** @var Inpsyde_Autoload_Rule_Interface $rule */
		foreach ( $this->rules as $rule )
			if ( $rule->load( $name ) )
				return;
	}
}