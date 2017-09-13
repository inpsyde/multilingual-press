<?php # -*- coding: utf-8 -*-
/**
 * Update changed languages for the language manager.
 *
 * Created by Mlp_Language_Manager_Controller.
 * update_languages() is registered as callback for the action
 * "admin_post_mlp_update_languages"
 *
 * @author  Inpsyde GmbH, MarketPress, toscho
 * @version 2013.12.23
 * @license GPL
 * @package MultilingualPress\Models\LanguageManager
 */
class Mlp_Language_Updater {

	/**
	 * Used to verify the request by its nonce.
	 *
	 * @type Mlp_Options_Page_Data
	 */
	private $page_data;

	/**
	 * Used to get the current page to limit the items to compare.
	 *
	 * @type Mlp_Browsable
	 */
	private $pagination_data;

	/**
	 * Compares the difference between exiting and changed items.
	 *
	 * @type Mlp_Array_Diff
	 */
	private $array_diff;

	/**
	 * Get existing items from database and store changes.
	 *
	 * @type Mlp_Data_Access
	 */
	private $db;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Options_Page_Data $page_data
	 * @param Mlp_Browsable         $pagination_data
	 * @param Mlp_Array_Diff        $array_diff
	 * @param Mlp_Data_Access       $db
	 */
	public function __construct(
		Mlp_Options_Page_Data $page_data,
		Mlp_Browsable $pagination_data,
		Mlp_Array_Diff $array_diff,
		Mlp_Data_Access $db
	) {

		$this->page_data       = $page_data;
		$this->pagination_data = $pagination_data;
		$this->array_diff      = $array_diff;
		$this->db              = $db;
	}

	/**
	 * Combine the work of all other methods.
	 *
	 * @return void
	 */
	public function update_languages() {

		$new    = $this->validate_request();
		$old    = $this->get_existing_items();
		$diff   = $this->array_diff->get_difference( $old, $new );
		$amount = $this->update_changed_items( $diff );

		wp_safe_redirect( $this->get_url( $amount ) );
		mlp_exit();
	}

	/**
	 * Check if current request is allowed and complete.
	 *
	 * @return array
	 */
	private function validate_request() {

		check_admin_referer(
			$this->page_data->get_nonce_action(),
			$this->page_data->get_nonce_name()
		);

		if ( empty( $_POST['languages'] ) ) {
			mlp_exit( 'invalid request' );
		}

		return (array) $_POST['languages'];
	}

	/**
	 * Fetch and prepare existing items for the current page from database.
	 *
	 * @return array
	 */
	private function get_existing_items() {

		$page   = $this->pagination_data->get_current_page();
		$params = array(
			'page' => $page,
		);
		$before = $this->db->get_items( $params );
		$return = array();

		foreach ( $before as $id => $data ) {
			$return[ $id ] = (array) $data;
		}

		return $return;
	}

	/**
	 * Store changes in database.
	 *
	 * @param  array   $diff
	 * @return integer Number of changed items.
	 */
	private function update_changed_items( array $diff ) {

		$amount = count( $diff );

		if ( 0 === $amount ) {
			return 0;
		}

		$this->db->update_items_by_id(
			$diff,
			array(
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
			)
		);

		return $amount;
	}

	/**
	 * Get URL for the redirect.
	 *
	 * @param  integer $amount
	 * @return string
	 */
	private function get_url( $amount ) {

		$url = filter_input( INPUT_POST, '_wp_http_referer' );

		if ( 0 === $amount ) {
			return remove_query_arg( 'msg', $url );
		}

		return add_query_arg( 'msg', "updated-$amount", $url );
	}
}
