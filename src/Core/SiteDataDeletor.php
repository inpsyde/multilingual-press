<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;

/**
 * Deletes all plugin-specific data when a site is deleted.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
class SiteDataDeletor {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var SiteSettingsRepository
	 */
	private $site_settings_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ContentRelations       $content_relations        Content relations API object.
	 * @param SiteRelations          $site_relations           Site relations API object.
	 * @param SiteSettingsRepository $site_settings_repository Site settings repository object.
	 */
	public function __construct(
		ContentRelations $content_relations,
		SiteRelations $site_relations,
		SiteSettingsRepository $site_settings_repository
	) {

		$this->content_relations = $content_relations;

		$this->site_relations = $site_relations;

		$this->site_settings_repository = $site_settings_repository;
	}

	/**
	 * Deletes all plugin-specific data of the site with the given ID.
	 *
	 * @since    3.0.0
	 * @wp-hook  delete_blog
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	public function delete_site_data( $site_id ) {

		$this->content_relations->delete_relations_for_site( $site_id );

		$this->site_relations->delete_relation( $site_id );

		$settings = $this->site_settings_repository->get_settings();
		if ( isset( $settings[ $site_id ] ) ) {
			unset( $settings[ $site_id ] );

			$this->site_settings_repository->set_settings( $settings );
		}
	}
}
