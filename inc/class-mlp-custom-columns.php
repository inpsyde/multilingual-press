<?php
/**
 * MultilingualPress custom columns class
 * Credits to http://wordpress.stackexchange.com/users/2487/bainternet
 * 
 * @version 0.5a
 * 
 */

/**
 * Changelog
 * 
 * 0.5a
 * - added filters for module support
 * 
 */
class mlp_custom_columns extends inpsyde_multilingualpress {

	static protected $class_object = NULL;

	public static function init() {

		if ( NULL == self::$class_object ) {
			self::$class_object = new self;
		}
		return self::$class_object;
	}

	public function __construct() {

		add_filter( 'wpmu_blogs_columns', array( $this, 'get_id' ) );
		add_action( 'manage_sites_custom_column', array( $this, 'add_columns' ), 10, 2 );
		//add_action( 'manage_blogs_custom_column', array( $this, 'add_columns' ), 10, 2 );
	}

	public function add_columns( $column_name, $blog_id ) {

		//render column value
		if ( 'mlp_interlinked' === $column_name ) {
                    
			switch_to_blog( $blog_id );
                        
			$interlinked = mlp_get_available_languages_titles();
			if ( ! is_array( $interlinked ) ) return;
			$url = network_admin_url();
			
                        echo "<div class=\"mlp_interlinked_blogs\" >";
			foreach( $interlinked AS $interlinked_blog_id => $interlinked_blog_title ) {
				
				if ( $interlinked_blog_id == $blog_id ) continue;
				
				//echo "<img src=\"" . mlp_get_language_flag( $interlinked_blog_id ) . "\" alt=\"{$interlinked_blog_title}\" />&nbsp;" . $interlinked_blog_title . "<br />";	
                                echo "<img src=\"" . mlp_get_language_flag( $interlinked_blog_id ) . "\" alt=\"{$interlinked_blog_title}\" title=\"{$interlinked_blog_title}\" />&nbsp;";	     
                        }
                        echo "</div>";
                        
			restore_current_blog();
		}
                
                $column_name = apply_filters( 'mlp_add_custom_columns', $column_name, $blog_id );
                
		return $column_name;
	}

	// Add in a column header
	public function get_id( $columns ) {

		//add extra header to table
		$columns[ 'mlp_interlinked' ] = __( 'Interlinked with', $this->get_textdomain() );
                
                $columns = apply_filters( 'mlp_add_custom_columns_header', $columns );

		return $columns;
	}
}
?>