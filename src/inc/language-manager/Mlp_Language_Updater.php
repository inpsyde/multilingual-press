<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

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
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Used to get the current page to limit the items to compare.
	 *
	 * @type Mlp_Browsable
	 */
	private $pagination_data;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Browsable         $pagination_data
	 * @param Mlp_Array_Diff        $array_diff
	 * @param Mlp_Data_Access       $db
	 * @param Nonce                 $nonce Nonce object.
	 */
	public function __construct(
		Mlp_Browsable         $pagination_data,
		Mlp_Array_Diff        $array_diff,
		Mlp_Data_Access       $db,
		Nonce $nonce
	) {

		$this->pagination_data = $pagination_data;
		$this->array_diff      = $array_diff;
		$this->db              = $db;

		$this->nonce = $nonce;
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
		\Inpsyde\MultilingualPress\call_exit();
	}

	/**
	 * Check if current request is allowed and complete.
	 *
	 * @return array
	 */
	private function validate_request() {

		if ( ! $this->nonce->is_valid() ) {
			\Inpsyde\MultilingualPress\call_exit( 'invalid request' );
		}

		if ( empty ( $_POST[ 'languages' ] ) )
			\Inpsyde\MultilingualPress\call_exit( 'invalid request' );

		return (array) $_POST[ 'languages' ];
	}

	/**
	 * Fetch and prepare existing items for the current page from database.
	 *
	 * @return array
	 */
	private function get_existing_items() {

		$page   = $this->pagination_data->get_current_page();
		$params = [ 'page' => $page ];
		$before = $this->db->get_items( $params );
		$return = [];

		foreach ( $before as $id => $data )
			$return[ $id ] = (array) $data;

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

		if ( 0 === $amount )
			return 0;

		$this->db->update_items_by_id(
			$diff,
			[ '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d' ]
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

		$url = $_POST[ '_wp_http_referer' ];

		if ( 0 === $amount )
			return remove_query_arg( 'msg', $url );

		return add_query_arg( 'msg', "updated-$amount", $url );
	}
}
