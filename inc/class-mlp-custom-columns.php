<?php
/**
 * MultilingualPress custom columns class
 * 
 * @version 0.5.1a
 * @since   0.5.0
 */

/**
 * Changelog
 * 
 * 0.5a
 * - added filters for module support
 * 
 */
class Mlp_Custom_Columns extends Inpsyde_Multilingualpress {

	static protected $class_object = NULL;
	
	/**
	 * Init class
	 * 
	 * @since  0.1
	 * @return object
	 */
	public static function init() {

		if ( NULL == self::$class_object ) {
			self::$class_object = new self;
		}
		return self::$class_object;
	}
	
	/**
	 * Constructor
	 * Init methods in WP
	 * 
	 * @since  0.1
	 * @return void
	 */
	public function __construct() {

		add_filter( 'wpmu_blogs_columns', array( $this, 'get_id' ) );
		add_action( 'manage_sites_custom_column', array( $this, 'add_columns' ), 10, 2 );
		//add_action( 'manage_blogs_custom_column', array( $this, 'add_columns' ), 10, 2 );
	}
	
	/**
	 * Add colums to blog view
	 * 
	 * @since  0.1
	 * @param  $column_name  string
	 * @param  $blog_id      integer
	 * @return $column_name  string
	 */
	public function add_columns( $column_name, $blog_id ) {

		//render column value
		if ( 'mlp_interlinked' === $column_name ) {
					
			switch_to_blog( $blog_id );
			
			$interlinked = mlp_get_available_languages_titles();
			
			if ( ! is_array( $interlinked ) || 
				 ( isset( $interlinked[4] ) && '-1' === $interlinked[4] ) ) {
				_e( 'nothing', $this->get_textdomain() );
				return;
			}
			
			$url = network_admin_url();
			
			echo '<div class="mlp_interlinked_blogs">';
			foreach( $interlinked AS $interlinked_blog_id => $interlinked_blog_title ) {
				
				if ( $interlinked_blog_id == $blog_id )
					continue;
				
				//echo "<img src=\"" . mlp_get_language_flag( $interlinked_blog_id ) 
				//	. "\" alt=\"{$interlinked_blog_title}\" />&nbsp;" 
				//	. $interlinked_blog_title . "<br />";
				echo '<img src="' 
					. mlp_get_language_flag( $interlinked_blog_id ) 
					. '" alt="' . $interlinked_blog_title 
					. '" title="' . $interlinked_blog_title 
					. '" class="mlp_interlinked_blog_' . $interlinked_blog_id
					. '" width="16" height="11"'
					. '/>&nbsp;';
			}
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
	 * @return  $columns  Array
	 */
	public function get_id( $columns ) {
		
		//add extra header to table
		$columns[ 'mlp_interlinked' ] = __( 'Interlinked with', $this->get_textdomain() );
		
		$columns = apply_filters( 'mlp_add_custom_columns_header', $columns );
		
		return $columns;
	}
	
} // end class
?>