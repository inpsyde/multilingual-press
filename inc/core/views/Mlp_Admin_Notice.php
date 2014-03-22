<?php # -*- coding: utf-8 -*-
/**
 * Show an admin notice.
 *
 * @author     toscho
 * @since      2013.08.26
 * @version    2013.08.26
 * @package    MultilingualPress
 * @subpackage Backend
 */
class Mlp_Admin_Notice {

	protected $msg;
	protected $attrs = array();
	protected $type = 'error';

	/**
	 * Constructor
	 *
	 * @param string $msg
	 * @param string $attrs HTML attributes. 'class' should be 'error' or 'updated'.
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

		$html = new Mlp_Html;
		$attrs = $html->array_to_attrs( $this->attrs );

		$msg = wpautop( $this->msg );
		$str = "<div$attrs>$msg</div>";
		print $str;

		return $str;
	}
}