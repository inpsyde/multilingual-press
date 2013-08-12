<?php
/**
 * Custom Columns
 *
 * @author		fb, rw, ms, th
 * @package		mlp
 * @subpackage	columns
 *
 */

class Mlp_Custom_Columns extends Multilingual_Press {

	/**
	 * The class object
	 *
	 * @static
	 * @since  0.1
	 * @var	string
	 */
	static protected $class_object = NULL;

	/**
	 * Init class
	 *
	 * @since  0.1
	 * @return object
	 */
	public static function init() {
		if ( NULL == self::$class_object )
			self::$class_object = new self;
		return self::$class_object;
	}

	/**
	 * Constructor
	 * Init methods in WP
	 *
	 * @since	0.1
	 * @uses	add_filter
	 * @return	void
	 */
	public function __construct() {

		add_filter( 'wpmu_blogs_columns', array( $this, 'get_id' ) );
		add_filter( 'manage_sites_custom_column', array( $this, 'add_columns' ), 10, 2 );
	}

	/**
	 * Add colums to blog view
	 *
	 * @since	0.1
	 * @param	$column_name  string
	 * @param	$blog_id	  integer
	 * @uses	switch_to_blog, mlp_get_available_languages_titles, _e, network_admin_url
	 * 			mlp_get_language_flag, restore_current_blog, apply_filters
	 * @return	$column_name  string
	 */
	public function add_columns( $column_name, $blog_id ) {

		//render column value
		if ( 'mlp_interlinked' === $column_name ) {

			switch_to_blog( $blog_id );

			$interlinked = mlp_get_available_languages_titles();

			if ( count( $interlinked ) < 2 || ! is_array( $interlinked ) ) {
				_e( 'nothing', 'multilingualpress' );
				return;
			}

			$linked_blogs = array();

			echo '<div class="mlp_interlinked_blogs">';

			foreach ( $interlinked as $interlinked_blog_id => $interlinked_blog_title ) {

				if ( $interlinked_blog_id == $blog_id )
					continue;

				$linked_blogs[] = sprintf(
					'<a href="%1$s">%2$s</a>',
					get_blogaddress_by_id( $interlinked_blog_id ),
					esc_html( $interlinked_blog_title )
				);
			}

			empty( $linked_blogs ) || print join( ' | ', $linked_blogs );
			echo '</div>';

			restore_current_blog();
		}

		$column_name = apply_filters( 'mlp_add_custom_columns', $column_name, $blog_id );

		return $column_name;
	}

	/**
	 * Add in a column header
	 * Use Filter Hook mlp_add_custom_columns_header for custom string
	 *
	 * @since   0.7.1a
	 * @param   $columns  Array
	 * @uses	apply_filters
	 * @return  $columns  Array
	 */
	public function get_id( $columns ) {

		//add extra header to table
		$columns[ 'mlp_interlinked' ] = __( 'Interlinked with', 'multilingualpress' );

		$columns = apply_filters( 'mlp_add_custom_columns_header', $columns );

		return $columns;
	}
} // end class
?>