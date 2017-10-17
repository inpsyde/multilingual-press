<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\SiteRelations;

use Inpsyde\MultilingualPress\API\SiteRelations;

/**
 * Site relations API.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\SiteRelations
 * @since   3.0.0
 */
class API {

	/**
	 * @var SiteRelations
	 */
	private $api;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations $api Site relations API object.
	 */
	public function __construct( SiteRelations $api ) {

		$this->api = $api;
	}

	/**
	 * Returns an array holding the IDs of all sites related to the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return int[] The array holding the IDs of all sites related to the site with the given ID.
	 */
	public function get_related_site_ids( int $site_id ): array {

		return $this->api->get_related_site_ids( $site_id );
	}
}
