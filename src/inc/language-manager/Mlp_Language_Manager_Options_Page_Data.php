<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Factory\TypeFactory;
use Inpsyde\MultilingualPress\Common\Type\Setting;
use Inpsyde\MultilingualPress\Common\Type\URL;

/**
 * Class Mlp_Language_Manager_Options_Page_Data
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Manager_Options_Page_Data implements Setting {

	/**
	 * @var string
	 */
	private $page_title;

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * @param string      $page_title
	 * @param TypeFactory $type_factory Type factory object.
	 */
	public function __construct( $page_title, TypeFactory $type_factory ) {

		$this->page_title = (string) $page_title;

		$this->type_factory = $type_factory;
	}

	/**
	 * Returns the action name for the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Action name.
	 */
	public function action() {

		return 'mlp_update_languages';
	}

	/**
	 * Returns the nonce name for the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce name.
	 */
	public function nonce_name() {

		return 'mlp_language_table_nonce';
	}

	/**
	 * Returns the title of the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Setting title.
	 */
	public function title() {

		return $this->page_title;
	}

	/**
	 * Returns the URL to be used in the according form.
	 *
	 * @since 3.0.0
	 *
	 * @return URL URL to submit updates to.
	 */
	public function url() {

		return $this->type_factory->create_url( [
			admin_url( 'admin-post.php' ),
		] );
	}

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Mlp_Language_Manager_Options_Page_Data::get_action}.
	 *
	 * @return string
	 */
	public function get_action_name() {

		// TODO: Adapt the following as soon as the class got refactored.

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Common\Type\Mlp_Language_Manager_Options_Page_Data::get_action'
		);

		return $this->action();
	}

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Mlp_Language_Manager_Options_Page_Data::get_url}.
	 *
	 * @return string
	 */
	public function get_form_action() {

		// TODO: Adapt the following as soon as the class got refactored.

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'(string) Inpsyde\MultilingualPress\Common\Type\Mlp_Language_Manager_Options_Page_Data::get_url'
		);

		return (string) $this->url();
	}

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Mlp_Language_Manager_Options_Page_Data::get_action}.
	 *
	 * @return string
	 */
	public function get_nonce_action() {

		// TODO: Adapt the following as soon as the class got refactored.

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Common\Type\Mlp_Language_Manager_Options_Page_Data::get_action'
		);

		return $this->action();
	}
}
