<?php
/**
 * Multilingual Press custom columns class
 * Credits to http://wordpress.stackexchange.com/users/2487/bainternet
 * 
 * @version 1.0
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
		add_action( 'manage_blogs_custom_column', array( $this, 'add_columns' ), 10, 2 );
		//add_action( 'admin_footer', array( $this, 'add_style' ) );
	}

	public function add_columns( $column_name, $blog_id ) {

		//render column value
		if ( 'mlp_interlinked' === $column_name ) {
			switch_to_blog( $blog_id );
			$interlinked = mlp_get_available_languages_titles();
			if ( ! is_array( $interlinked ) ) return;
			$url = network_admin_url();
			
			foreach( $interlinked AS $interlinked_blog_id => $interlinked_blog_title ) {
				
				if ( $interlinked_blog_id == $blog_id ) continue;
				
				echo "<img src=\"" . mlp_get_language_flag( $interlinked_blog_id ) . "\" alt=\"{$interlinked_blog_title}\" />&nbsp;<a href=\"{$url}site-info.php?id={$blog_id}\">" . $interlinked_blog_title . "</a><br />";
				
			}
			//echo get_blog_option( $blog_id, 'blog_expire', "Default Value To Show if none" );
		}
		return $column_name;
	}

	// Add in a column header
	public function get_id( $columns ) {

		//add extra header to table
		$columns[ 'mlp_interlinked' ] = __( 'Interlinked with', $this->get_textdomain() );

		return $columns;
	}

	public function add_style() {

		//echo '<style>#blog_id { width:7%; }</style>';
	}

}
?>