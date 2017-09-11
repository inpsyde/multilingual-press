<?php
/**
 * Custom Columns
 *
 * Adds a column to a WP_List_Table view
 *
 * @author      fb, rw, ms, th, toscho
 * @package     MultilingualPress
 * @subpackage  backend
 */
class Mlp_Custom_Columns {

	/**
	 * Column ID, header and content callback
	 *
	 * @type array
	 */
	protected $settings = array();

	/**
	 * Constructor
	 * Init methods in WP
	 *
	 * @wp-hook inpsyde_mlp_loaded
	 * @param   array $settings
	 *              id               = column id
	 *              header           = header text
	 *              content_callback = render column content
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Render column content.
	 *
	 * @wp-hook manage_sites_custom_column
	 * @since   0.1
	 * @param   string $name Column ID
	 * @param   int $id      ID of the current element
	 * @return  void
	 */
	public function render_column( $name, $id ) {

		if ( $this->settings['id'] === $name ) {
			echo wp_kses_post( call_user_func( $this->settings['content_callback'], $name, $id ) );
		}
	}

	/**
	 * Add a column header
	 * Use Filter Hook mlp_add_custom_columns_header for custom string
	 *
	 * @since   0.7.1a
	 * @param   array $columns Existing columns
	 * @return  array
	 */
	public function add_header( array $columns ) {

		$columns[ $this->settings['id'] ] = $this->settings['header'];

		return $columns;
	}
}
