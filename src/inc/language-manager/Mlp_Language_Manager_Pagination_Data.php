<?php # -*- coding: utf-8 -*-
/**
 * Pagination information for Language Manager.
 *
 * Evaluates $_REQUEST to get the current page, because this is used for the
 * view and when the data is saved.
 *
 * @author  Inpsyde GmbH, MarketPress, toscho
 * @version 2013.12.22
 * @package MultilingualPress\Pagination\LanguageManager
 */
class Mlp_Language_Manager_Pagination_Data implements Mlp_Browsable {

	/**
	 * Hold data access instance.
	 *
	 * @type Mlp_Data_Access
	 */
	private $data;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Data_Access $data
	 */
	public function __construct( Mlp_Data_Access $data ) {
		$this->data = $data;
	}

	/**
	 * (non-PHPdoc)
	 * @see Mlp_Browsable::get_total_items()
	 */
	public function get_total_items() {

		return $this->data->get_total_items_number();
	}

	/**
	 * (non-PHPdoc)
	 * @see Mlp_Browsable::get_total_pages()
	 */
	public function get_total_pages() {

		// ceil() returns a float, we need an integer.
		return (int) ceil(
			$this->get_total_items() / $this->get_items_per_page()
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mlp_Browsable::get_items_per_page()
	 */
	public function get_items_per_page() {

		return $this->data->get_page_size();
	}

	/**
	 * (non-PHPdoc)
	 * @see Mlp_Browsable::get_current_page()
	 */
	public function get_current_page() {

		// can be sent per GET or POST
		if ( empty ( $_REQUEST[ 'paged' ] ) || 2 > $_REQUEST[ 'paged' ] )
			return 1;

		// fix calls to page 99 when there are just 10 possible pages
		$page = min( $_REQUEST[ 'paged' ], $this->get_total_pages() );

		return absint( $page );
	}
}