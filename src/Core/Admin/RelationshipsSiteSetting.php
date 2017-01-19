<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

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
	 * Returns the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string The markup for the site setting.
	 */
	public function markup( $site_id ) {

		// TODO: Adapt to be used on Edit Site as well.
		return $this->get_relationships() . sprintf(
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
	public function title() {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Relationships', 'multilingual-press' ),
			esc_attr( $this->id )
		);
	}

	/**
	 * Returns the markup for all relationships.
	 *
	 * @return string The markup for all relationships.
	 */
	private function get_relationships() {

		$site_ids = (array) get_network_option( null, 'inpsyde_multilingual', [] );
		if ( ! $site_ids ) {
			return '';
		}

		$site_ids = array_unique( array_map( 'intval', array_keys( $site_ids ) ) );

		return array_reduce( $site_ids, function ( $relationships, $site_id ) {

			switch_to_blog( $site_id );
			$site_name = get_bloginfo( 'name' );
			restore_current_blog();

			return $relationships . sprintf(
					'<p><label for="%3$s"><input type="checkbox" name="%s[]" value="%2$d" id="%3$s">%1$s</label></p>',
					sprintf(
						// translators: 1 = site name, 2 = site language
						esc_html_x( '%1$s - %2$s', 'Site relationships', 'multilingual-press' ),
						$site_name,
						\Inpsyde\MultilingualPress\get_site_language( $site_id, false )
					),
					esc_attr( '' ),
					esc_attr( "{$this->id}-{$site_id}" ),
					esc_attr( SiteSettingsRepository::NAME_RELATIONSHIPS )
				);
		}, '' );
	}
}
