<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\NetworkState;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

use function Inpsyde\MultilingualPress\get_site_language;

/**
 * MultilingualPress "Relationships" site setting.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class RelationshipsSiteSetting implements SiteSettingViewModel {

	/**
	 * @var string
	 */
	private $id = 'mlp-site-relations';

	/**
	 * @var SiteSettingsRepository
	 */
	private $repository;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingsRepository $repository     Site settings repository object.
	 * @param SiteRelations          $site_relations Site relations API object.
	 */
	public function __construct( SiteSettingsRepository $repository, SiteRelations $site_relations ) {

		$this->repository = $repository;

		$this->site_relations = $site_relations;
	}

	/**
	 * Returns the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string The markup for the site setting.
	 */
	public function markup( int $site_id ): string {

		return $this->get_relationships( $site_id ) . sprintf(
				'<p class="description">%s</p>',
				esc_html__(
					'You can connect this site only to sites with an assigned language. Other sites will not show up here.',
					'multilingual-press'
				)
			);
	}

	/**
	 * Returns the title of the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the site setting.
	 */
	public function title(): string {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Relationships', 'multilingual-press' ),
			esc_attr( $this->id )
		);
	}

	/**
	 * Returns the markup for all relationships.
	 *
	 * @param int $base_site_id Current site ID.
	 *
	 * @return string The markup for all relationships.
	 */
	private function get_relationships( int $base_site_id ): string {

		$site_ids = $this->repository->get_site_ids( [ $base_site_id ] );
		if ( ! $site_ids ) {
			return '';
		}

		$network_state = NetworkState::from_globals();

		$relationships = array_reduce( $site_ids, function ( $relationships, $site_id ) use ( $base_site_id ) {

			switch_to_blog( $site_id );

			$site_name = get_bloginfo( 'name' );

			$related_site_ids = $this->site_relations->get_related_site_ids( (int) $site_id );

			return $relationships . sprintf(
					'<p><label for="%3$s"><input type="checkbox" name="%4$s[]" value="%2$d" id="%3$s"%5$s>%1$s</label></p>',
					sprintf(
						// translators: 1 = site name, 2 = site language
						esc_html_x( '%1$s - %2$s', 'Site relationships', 'multilingual-press' ),
						$site_name,
						get_site_language( $site_id, false )
					),
					esc_attr( $site_id ),
					esc_attr( "{$this->id}-{$site_id}" ),
					esc_attr( SiteSettingsRepository::NAME_RELATIONSHIPS ),
					checked( in_array( $base_site_id, $related_site_ids, true ), true, false )
				);
		}, '' );

		$network_state->restore();

		return $relationships;
	}
}
