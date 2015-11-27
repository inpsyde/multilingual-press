<?php
/**
 * Show the redirect status in the sites list.
 *
 * @version 2014.04.27
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect_Column {

	/**
	 * @var string
	 */
	private $option_name;

	/**
	 * @var string
	 */
	private $image_url;

	/**
	 * Constructor.
	 *
	 * @param string $option_name
	 * @param string $image_url
	 */
	public function __construct( $option_name, $image_url ) {

		$this->option_name = $option_name;
		$this->image_url   = $image_url;
	}

	/**
	 * Initial set up.
	 *
	 * @return void
	 */
	public function setup() {

		$columns = $this->get_column_handler();

		add_filter(
			'wpmu_blogs_columns',
			array ( $columns, 'add_header' )
		);
		add_action(
			'manage_sites_custom_column',
			array ( $columns, 'render_column' ), 10, 2
		);
	}

	/**
	 * Render cell content.
	 *
	 * @param  string $column_name Not used, but passed.
	 * @param  int    $blog_id
	 * @return string
	 */
	public function render_cell(
		/** @noinspection PhpUnusedParameterInspection */
		$column_name, $blog_id
	) {

		if ( ! get_blog_option( $blog_id, 'inpsyde_multilingual_redirect' ) )
			return '';

		return "<img src='{$this->image_url}' alt='x'>";
	}

	/**
	 * Create the column handler class.
	 *
	 * @return Mlp_Custom_Columns
	 */
	private function get_column_handler() {

		$data = array (
			'id'               => 'mlp_redirect',
			'header'           => __( 'Redirect', 'multilingualpress' ),
			'content_callback' => array ( $this, 'render_cell' )
		);

		return new Mlp_Custom_Columns( $data );
	}
}
