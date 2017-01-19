<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;
use wpdb;

/**
 * Site duplication "Based on site" setting.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
final class BasedOnSiteSetting implements SiteSettingViewModel {

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * @var string
	 */
	private $id = 'mlp-base-site-id';

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param wpdb $db WordPress database object.
	 */
	public function __construct( wpdb $db ) {

		$this->db = $db;
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

		return sprintf(
			'<select id="%2$s" name="blog[%3$s]" autocomplete="off">%1$s</select>',
			$this->get_options(),
			esc_attr( $this->id ),
			esc_attr( SiteDuplicator::NAME_BASED_ON_SITE )
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
			esc_html__( 'Based on site', 'multilingual-press' ),
			esc_attr( $this->id )
		);
	}

	/**
	 * Returns the markup for all option tags.
	 *
	 * @return string The markup for all option tags.
	 */
	private function get_options() {

		$options = '<option value="0">' . esc_html__( 'Choose site', 'multilingual-press' ) . '</option>';

		$sites = (array) $this->get_all_sites();
		if ( $sites ) {
			$options = array_reduce( $sites, function ( $options, array $site ) {

				$url = $site['domain'] . ( '/' === $site['path'] ? '' : $site['path'] );

				return $options . '<option value="' . esc_attr( $site['id'] ) . '">' . esc_url( $url ) . '</option>';
			}, $options );
		}

		return $options;
	}

	/**
	 * Returns all existing sites.
	 *
	 * @return string[][] An array with site data arrays.
	 */
	private function get_all_sites() {

		$query = "SELECT blog_id AS id, domain, path FROM {$this->db->blogs} WHERE deleted = 0 AND site_id = %s";
		$query = $this->db->prepare( $query, $this->db->siteid );

		return $this->db->get_results( $query, ARRAY_A );
	}
}
