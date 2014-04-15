<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Language_Api
 *
 * Not complete yet.
 *
 * @version 2014.04.11
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Api implements Mlp_Language_Api_Interface {

	/**
	 *
	 *
	 * @var Mlp_Language_Db_Access
	 */
	private $db;

	/**
	 *
	 *
	 * @var Inpsyde_Property_List_Interface
	 */
	private $data;

	/**
	 * Table name including base prefix.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor.
	 *
	 * @wp-hook plugins_loaded
	 * @param   Inpsyde_Property_List_Interface $data
	 * @param   string                          $table_name
	 */
	public function __construct(
		Inpsyde_Property_List_Interface $data,
		$table_name
	) {
		$this->data = $data;
		$this->db   = new Mlp_Language_Db_Access( $table_name );
		$this->table_name = $GLOBALS[ 'wpdb' ]->base_prefix . $table_name;

		add_action( 'wp_loaded', array ( $this, 'load_language_manager' ) );
		add_filter( 'mlp_language_api', array ( $this, 'get_instance' ) );
	}

	/**
	 * (non-PHPdoc)
	 * @see Mlp_Language_Api_Interface::get_db()
	 */
	public function get_db() {
		return $this->db;
	}

	/**
	 * Access to this instance from the outside.
	 *
	 * Usage:
	 * <code>
	 * $mlp_language_api = apply_filters( 'mlp_language_api', NULL );
	 * if ( is_a( $mlp_language_api, 'Mlp_Language_Api_Interface' ) )
	 * {
	 *     // do something
	 * }
	 * </code>
	 */
	public function get_instance() {
		return $this;
	}

	public function load_language_manager()
	{
		new Mlp_Language_Manager_Controller( $this->data, $this->db );
	}

	/**
	 * Get language names for related blogs.
	 *
	 * @see Mlp_Helpers::get_available_languages_titles()
	 * @param  int $base_site
	 * @return array
	 */
	public function get_site_languages( $base_site = 0 ) {

		static $languages;

		$related_blogs = '';

		if ( empty ( $languages ) )
			$languages = get_site_option( 'inpsyde_multilingual' );

		if ( 0 !== $base_site )
			$related_blogs = get_blog_option( get_current_blog_id(), 'inpsyde_multilingual_blog_relationship' );

		if ( empty ( $related_blogs ) && 0 !== $base_site )
			return array ();

		if ( ! is_array( $languages ) )
			return array ();

		$options = array ();

		foreach ( $languages as $language_blogid => $language_data ) {

			// Filter out blogs that are not related
			if ( is_array( $related_blogs ) && ! in_array( $language_blogid, $related_blogs ) && 0 !== $base_site )
				continue;

			$lang = '';

			if ( isset ( $language_data[ 'text' ] ) )
				$lang = $language_data[ 'text' ];

			if ( '' === $lang )
				$lang = $this->get_lang_data_by_iso( $language_data[ 'lang' ] );

			$options[ $language_blogid ] = $lang;
		}

		return $options;
	}

	/**
	 * @param  string $iso Something like de_AT
	 * @return string
	 */
	public function get_lang_data_by_iso( $iso ) {

		global $wpdb;

		$iso = str_replace( '_', '-', $iso );

		$query  = $wpdb->prepare(
					   "SELECT `native_name`
			FROM `{$this->table_name}`
			WHERE `http_name` = %s LIMIT 1",
						   $iso
		);
		$result = $wpdb->get_var( $query );

		return NULL === $result ? '' : $result;
	}
}