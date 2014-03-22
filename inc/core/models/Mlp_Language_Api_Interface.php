<?php # -*- coding: utf-8 -*-
interface Mlp_Language_Api_Interface {
	public function __construct(
		Inpsyde_Property_List_Interface $data,
		$table_name
	);
	/**
	 * Access to language database handler.
	 *
	 * @return Mlp_Data_Access
	 */
	public function get_db();

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
	 *
	 * @return Mlp_Language_Api_Interface
	 */
	public function get_instance();
}