<?php # -*- coding: utf-8 -*-
/**
 * Interface Mlp_Language_Api_Interface
 *
 * @version 2014.07.14
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Language_Api_Interface {

	/**
	 * Access to language database handler.
	 *
	 * @return Mlp_Data_Access
	 */
	public function get_db();

	/**
	 * Access to this instance from the outside.
	 *
	 * Usage:
	 * <code>
	 * $mlp_language_api = apply_filters( 'mlp_language_api', null );
	 * if ( is_a( $mlp_language_api, 'Mlp_Language_Api_Interface' ) ) {
	 *     // do something
	 * }
	 * </code>
	 *
	 * @return Mlp_Language_Api_Interface
	 */
	public function get_instance();


	/**
	 * Ask for specific translations with arguments.
	 *
	 * Possible arguments are:
	 *
	 *     - 'site_id'              Base site
	 *     - 'content_id'           post or term_taxonomy ID, *not* term ID
	 *     - 'type'                 see Mlp_Language_Api::get_request_type(),
	 *     - 'strict'               When true only matching exact translations will be included
	 *     - 'search_term'          if you want to translate a search
	 *     - 'post_type'            for post type archives
	 *     - 'include_base'         bool. Include the base site in returned list
	 *
	 * @param  array $args Optional. If left out, some magic happens.
	 * @return array Array of Mlp_Translation instances, site IDs are the keys
	 */
	public function get_translations( array $args = array() );

	/**
	 * @param  string $iso Something like de_AT
	 *
	 * @param string $field the field which should be queried
	 * @return mixed
	 */
	public function get_lang_data_by_iso( $iso, $field = 'native_name' );

	/**
	 * @param  int    $site_id
	 * @param  int    $content_id
	 * @param  string $type
	 * @return array
	 */
	public function get_related_content_ids( $site_id, $content_id, $type );

	/**
	 * Get language names for related blogs.
	 *
	 * @see Mlp_Helpers::get_available_languages_titles()
	 * @param  int $base_site
	 * @return array
	 */
	public function get_site_languages( $base_site = 0 );

	public function load_language_manager();

	/**
	 * @param  string $language Formatted like en_GB
	 * @param  int    $site_id
	 * @return Mlp_Url_Interface
	 */
	public function get_flag_by_language( $language, $site_id = 0 );

}
