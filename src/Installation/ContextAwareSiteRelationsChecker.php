<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\AdminNotice;

/**
 * Context-aware site relations checker implementation.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
final class ContextAwareSiteRelationsChecker implements SiteRelationsChecker {

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations $site_relations Site relations API object.
	 */
	public function __construct( SiteRelations $site_relations ) {

		$this->site_relations = $site_relations;
	}

	/**
	 * Checks if there are at least two sites related to each other, and renders an admin notice if not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not there are at least two sites related to each other.
	 */
	public function check_relations(): bool {

		if ( wp_doing_ajax() ) {
			return true;
		}

		if ( is_network_admin() ) {
			return true;
		}

		if ( ! is_super_admin() ) {
			return true;
		}

		if ( 1 < count( $this->site_relations->get_all_relations() ) ) {
			return true;
		}

		$this->render_admin_notice();

		return false;
	}

	/**
	 * Renders the admin notice.
	 *
	 * @return void
	 */
	private function render_admin_notice() {

		add_action( 'all_admin_notices', function () {

			$message = __(
				"You didn't set up any site relationships. You have to set up these first to use MultilingualPress. Please go to Network Admin &raquo; Sites &raquo; and choose a site to edit. Then go to the tab MultilingualPress and set up the relationships.",
				'multilingualpress'
			);

			( new AdminNotice( "<p>{$message}</p>", [
				'type' => 'error',
			] ) )->render();
		} );
	}
}
