<?php # -*- coding: utf-8 -*-
/**
 * Show an admin notice.
 *
 * @author     toscho
 * @since      2013.08.26
 * @version    2014.07.14
 * @package    MultilingualPress
 * @subpackage Backend
 */
class Mlp_Admin_Notice {

	/**
	 * @var string
	 */
	private $msg;

	/**
	 * @var array
	 */
	private $attrs = array();

	/**
	 * Constructor
	 *
	 * @param string $msg
	 * @param array $attrs HTML attributes. 'class' should be 'error' or 'updated'.
	 */
	public function __construct( $msg, $attrs = array() ) {

		$this->msg   = $msg;

		if ( empty ( $attrs ) )
			$attrs = array( 'class' => 'error' );
		elseif ( empty ( $attrs[ 'class' ] ) )
			$attrs[ 'class' ] = 'error';

		$this->attrs = $attrs;
	}

	/**
	 * Display the message.
	 *
	 * @return string
	 */
	public function show() {

		$html = new Mlp_Html();

		$attrs = $html->array_to_attrs( $this->attrs );

		$msg = wpautop( $this->msg );

		$str = "<div $attrs>$msg</div>";
		echo $str;

		return $str;
	}
}
