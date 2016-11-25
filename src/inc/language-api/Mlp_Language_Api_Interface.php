<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\Translation;

/**
 * Interface Mlp_Language_Api_Interface
 *
 * @version 2014.07.14
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Language_Api_Interface {

	/**
	 * Ask for specific translations with arguments.
	 *
	 * Possible arguments are:
	 *
	 *     - 'site_id'              Base site
	 *     - 'content_id'           post or term_taxonomy ID, *not* term ID
	 *     - 'type'                 see Mlp_Language_Api::get_request_type(),
	 *     - 'strict'               When TRUE only matching exact translations will be included
	 *     - 'search_term'          if you want to translate a search
	 *     - 'post_type'            for post type archives
	 *     - 'include_base'         bool. Include the base site in returned list
	 *
	 * @param  array $args Optional. If left out, some magic happens.
	 *
	 * @return Translation[] Array of Mlp_Translation instances, site IDs are the keys
	 */
	public function get_translations( array $args = [] );
}
