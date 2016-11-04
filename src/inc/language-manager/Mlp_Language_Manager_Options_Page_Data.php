<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\Setting;
use Inpsyde\MultilingualPress\Common\Type\URL;
use Inpsyde\MultilingualPress\Factory\TypeFactory;

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

		return 'update_multilingualpress_languages';
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
}
