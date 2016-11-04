<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\Setting;
use Inpsyde\MultilingualPress\Common\Type\URL;
use Inpsyde\MultilingualPress\Factory\TypeFactory;

/**
 * Class Mlp_Network_Site_Settings_Tab_Data
 *
 * @version 2015.06.30
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings_Tab_Data implements Setting {

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * @param TypeFactory $type_factory Type factory object.
	 */
	public function __construct( TypeFactory $type_factory ) {

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

		return 'save_multilingualpress_site_settings';
	}

	/**
	 * Returns the title of the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Setting title.
	 */
	public function title() {

		return '';
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
