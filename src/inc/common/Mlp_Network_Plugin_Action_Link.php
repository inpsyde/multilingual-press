<?php # -*- coding: utf-8 -*-

/**
 * Network plugin action link data.
 */
class Mlp_Network_Plugin_Action_Link {

	/**
	 * @var string[]
	 */
	private $link;

	/**
	 * @var bool
	 */
	private $prepend;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param string[] $link     The new plugin action link data.
	 * @param string   $position Optional. Add the link as first or last one? Defaults to 'last'.
	 */
	public function __construct( array $link, $position = 'last' ) {

		$this->link = $link;

		$this->prepend = $position !== 'last';
	}

	/**
	 * Add the new plugin action link to the existing ones.
	 *
	 * @wp-hook network_admin_plugin_action_links_$plugin_file
	 *
	 * @param string[] $links The current plugin action links.
	 *
	 * @return string[]
	 */
	public function add( array $links ) {

		if ( $this->prepend ) {
			return array_merge( $this->link, $links );
		}

		return array_merge( $links, $this->link );
	}
}
