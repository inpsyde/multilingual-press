<?php # -*- coding: utf-8 -*-
class Mlp_Language_Api implements Mlp_Language_Api_Interface {

	private $db, $data;

	public function __construct(
		Inpsyde_Property_List_Interface $data,
		$table_name
	) {
		$this->data = $data;
		$this->db   = new Mlp_Language_Db_Access( $table_name );

		add_action( 'wp_loaded', array ( $this, 'load_language_manager' ) );
		add_filter( 'mlp_language_api', array ( $this, 'get_instance' ) );
	}

	/**
	 * (non-PHPdoc)
	 * @see Mlp_Language_Api_Interface::get_db()
	 */
	public function get_db() {
		return $this->db;
	}

	/**
	 * Access to this instance from the outside.
	 *
	 * Usage:
	 * <code>
	 * $mlp_language_api = apply_filters( 'mlp_language_api', NULL );
	 * if ( is_a( $mlp_language_api, 'Mlp_Language_Api_Interface' ) )
	 * {
	 *     // do something
	 * }
	 * </code>
	 */
	public function get_instance() {
		return $this;
	}

	public function load_language_manager()
	{
		new Mlp_Language_Manager_Controller( $this->data, $this->db );
	}
}