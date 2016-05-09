<?php # -*- coding: utf-8 -*-

/**
 * Show the redirect status in the sites list.
 */
class Mlp_Redirect_Column {

	/**
	 * Constructor.
	 *
	 * @param $deprecated
	 * @param $deprecated_url
	 */
	public function __construct( $deprecated, $deprecated_url ) {
	}

	/**
	 * Initial setup.
	 *
	 * @return void
	 */
	public function setup() {

		$columns = $this->get_column_handler();

		add_filter( 'wpmu_blogs_columns', array( $columns, 'add_header' ) );
		add_action( 'manage_sites_custom_column', array( $columns, 'render_column' ), 10, 2 );
	}

	/**
	 * Renders cell content.
	 *
	 * @param string $column_name Not used, but passed.
	 * @param int    $blog_id
	 *
	 * @return string
	 */
	public function render_cell(
		/** @noinspection PhpUnusedParameterInspection */
		$column_name, $blog_id
	) {

		if ( ! get_blog_option( $blog_id, 'inpsyde_multilingual_redirect' ) ) {
			return '';
		}

		return '<span class="dashicons dashicons-yes"></span>';
	}

	/**
	 * Create the column handler class.
	 *
	 * @return Mlp_Custom_Columns
	 */
	private function get_column_handler() {

		$data = array(
			'id'               => 'mlp_redirect',
			'header'           => __( 'Redirect', 'multilingual-press' ),
			'content_callback' => array( $this, 'render_cell' ),
		);

		return new Mlp_Custom_Columns( $data );
	}
}
