<?php
/**
 * Multilingual Press Helperfunctions
 * Version: 1.1b
 * 
 */
if ( ! class_exists( 'inpsyde_multilingualpress_helpers' ) ) {

	class inpsyde_multilingualpress_helpers extends Inpsyde_Multilingualpress{}
}

	/**
	 * wrapper of internal inpsyde_multilingualpress:get_available_languages
	 * load the available languages  
	 *
	 * @access  public
	 * @since   0.1
	 * @return  array Available languages
	 */
	function mlp_get_available_languages() {

		$object = Inpsyde_Multilingualpress::get_object();
		return $object->get_available_languages();
	}

	/**
	 * wrapper of internal inpsyde_multilingualpress:: get_available_language_title
	 * load the available language titles  
	 *
	 * @access  public
	 * @since   0.5.3b
	 * @return  array Available languages
	 */
	function mlp_get_available_languages_titles() {

		$object = Inpsyde_Multilingualpress::get_object();
		return $object->get_available_languages_titles();
	}

	/**
	 * wrapper of internal inpsyde_multilingualpress function to get the element ID in other blogs for the selected element  
	 *
	 * @access  public
	 * @since   0.1
	 * @param   int $element_id ID of the selected element
	 * @param   string $type type of the selected element
	 * @param   int $blog_id ID of the selected blog
	 * @return  array linked elements
	 */
	function mlp_get_linked_elements( $element_id, $type = '', $blog_id = 0 ) {

		$object = Inpsyde_Multilingualpress::get_object();
		return $object->load_linked_elements( $element_id, $type, $blog_id );
	}

	/**
	 * wrapper of internal inpsyde_multilingualpress function for custom plugins to get activated on all language blogs  
	 *
	 * @access  public
	 * @since   0.1
	 * @param   int $element_id ID of the selected element
	 * @param   string $type type of the selected element
	 * @param   int $blog_id ID of the selected blog
	 * @param   string $hook name of the hook that will be executed
	 * @param   array $param parameters for the function
	 * @return  array linked elements
	 */
	function mlp_run_custom_plugin( $element_id, $type = '', $blog_id = 0, $hook, $param ) {

		$object = Inpsyde_Multilingualpress::get_object();
		return $object->run_custom_plugin( $element_id, $type, $blog_id, $hook, $param );
	}

	/**
	 * wrapper of internal inpsyde_multilingualpress function for function to get the url of the flag from a blogid  

	 * @access  public
	 * @since   0.1
	 * @param   int $blog_id ID of a blog
	 * @return  string url of the language image
	 */
	function mlp_get_language_flag( $blog_id = 0 ) {
		
		$object = Inpsyde_Multilingualpress::get_object();
		return $object->get_language_flag( $blog_id );
	}