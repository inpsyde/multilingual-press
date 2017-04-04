<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\NavMenu;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

/**
 * Handler for nav menu AJAX requests.
 *
 * @package Inpsyde\MultilingualPress\NavMenu
 * @since   3.0.0
 */
class AJAXHandler {

	/**
	 * AJAX action.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION = 'add_languages_to_nav_menu';

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var ItemRepository
	 */
	private $repository;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Nonce          $nonce      Nonce object.
	 * @param ItemRepository $repository Item repository object.
	 * @param Request        $request    HTTP request abstraction
	 */
	public function __construct( Nonce $nonce, ItemRepository $repository, Request $request ) {

		$this->nonce = $nonce;

		$this->repository = $repository;

		$this->request = $request;
	}

	/**
	 * Handles the AJAX request and sends an appropriate response.
	 *
	 * @since   3.0.0
	 * @wp-hook wp_ajax_{$action}
	 *
	 * @return void
	 */
	public function send_items() {

		if ( ! $this->is_request_valid() ) {
			wp_send_json_error();
		}

		$sites = $this->request->body_value( 'mlp_sites', INPUT_GET, FILTER_SANITIZE_NUMBER_INT, FILTER_FORCE_ARRAY );

		$items = $this->repository->get_items_for_sites( array_map( 'intval', (array) $sites ) );

		/**
		 * Contains the Walker_Nav_Menu_Edit class.
		 */
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

		wp_send_json_success( walk_nav_menu_tree( $items, 0, (object) [
			'after'       => '',
			'before'      => '',
			'link_after'  => '',
			'link_before' => '',
			'walker'      => new \Walker_Nav_Menu_Edit(),
		] ) );
	}

	/**
	 * Checks if the request is valid.
	 *
	 * @return bool Whether or not the request is valid.
	 */
	private function is_request_valid(): bool {

		return (
			current_user_can( 'edit_theme_options' )
			&& $this->nonce->is_valid()
			&& $this->request->body_value( 'mlp_sites', INPUT_GET, FILTER_SANITIZE_NUMBER_INT, FILTER_FORCE_ARRAY )
		);
	}
}
