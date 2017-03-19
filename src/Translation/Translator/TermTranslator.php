<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Translator;

use Inpsyde\MultilingualPress\Factory\TypeFactory;
use Inpsyde\MultilingualPress\Translation\Translator;

/**
 * Translator implementation for terms.
 *
 * @package Inpsyde\MultilingualPress\Translation\Translator
 * @since   3.0.0
 */
final class TermTranslator implements Translator {

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * @var \WP_Rewrite
	 */
	private $wp_rewrite;

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TypeFactory $type_factory Type factory object.
	 * @param \wpdb       $db           Database object.
	 */
	public function __construct( TypeFactory $type_factory, \wpdb $db ) {

		$this->type_factory = $type_factory;

		$this->db = $db;
	}

	/**
	 * Returns the translation data for the given site, according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $site_id Site ID.
	 * @param array $args    Optional. Arguments required to fetch translation. Defaults to empty array.
	 *
	 * @return array Translation data.
	 */
	public function get_translation( int $site_id, array $args = [] ): array {

		if ( empty( $args['content_id'] ) ) {
			return [];
		}

		// WP_Rewrite is instantiated after the service providers have been bootstrapped, so we can't use the container.
		if ( ! $this->wp_rewrite ) {
			global $wp_rewrite;

			$this->wp_rewrite = $wp_rewrite;
		}

		$term_taxonomy_id = (int) $args['content_id'];

		$translations = wp_cache_get( 'mlp_term_translations', 'mlp' );
		if ( isset( $translations[ $site_id ][ $term_taxonomy_id ] ) ) {
			return $translations[ $site_id ][ $term_taxonomy_id ];
		}

		switch_to_blog( $site_id );

		$data = $this->get_translation_data( $term_taxonomy_id );

		restore_current_blog();

		$translations[ $site_id ][ $term_taxonomy_id ] = $data;

		wp_cache_set( 'mlp_term_translations', $translations, 'mlp' );

		return $data;
	}

	/**
	 * Returns the translation data for the given term taxonomy ID.
	 *
	 * @param int $term_taxonomy_id Term taxonomy ID.
	 *
	 * @return array Translation data.
	 */
	private function get_translation_data( int $term_taxonomy_id ): array {

		$term = $this->get_term_by_term_taxonomy_id( $term_taxonomy_id );
		if ( ! $term ) {
			return [];
		}

		if ( is_admin() ) {
			return current_user_can( 'edit_terms', $term['taxonomy'] )
				? [
					'remote_url'   => $this->type_factory->create_url( [
						get_edit_term_link( (int) $term['term_id'], $term['taxonomy'] ),
					] ),
					'remote_title' => $term['name'],
				]
				: [];
		}

		return [
			'remote_url'   => $this->type_factory->create_url( [
				$this->get_public_url( (int) $term['term_id'], (string) $term['taxonomy'] ),
			] ),
			'remote_title' => $term['name'],
		];
	}

	/**
	 * Returns term data according to the given term taxonomy ID.
	 *
	 * @param int $term_taxonomy_id Term taxonomy ID.
	 *
	 * @return array Term data.
	 */
	private function get_term_by_term_taxonomy_id( int $term_taxonomy_id ): array {

		$cache_key = "term_with_ttid_$term_taxonomy_id";

		$term = wp_cache_get( $cache_key, 'mlp' );
		if ( is_array( $term ) ) {
			return $term;
		}

		$query = "
SELECT t.term_id, t.name, tt.taxonomy
FROM {$this->db->terms} t, {$this->db->term_taxonomy} tt
WHERE tt.term_id = t.term_id AND tt.term_taxonomy_id = %d
LIMIT 1";
		$query = $this->db->prepare( $query, $term_taxonomy_id );

		$term = $this->db->get_row( $query, ARRAY_A );
		if ( ! $term ) {
			$term = [];
		}

		wp_cache_set( $cache_key, $term, 'mlp' );

		return $term;
	}

	/**
	 * Prepares the taxonomy base before the URL is fetched.
	 *
	 * @param int    $term_id  Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return string Term archive URL.
	 */
	private function get_public_url( int $term_id, string $taxonomy ): string {

		$changed = $this->fix_term_base( $taxonomy );

		$url = get_term_link( $term_id, $taxonomy );
		if ( is_wp_error( $url ) ) {
			$url = '';
		}

		if ( $changed ) {
			$this->set_permastruct( $taxonomy, $changed );
		}

		return $url;
	}

	/**
	 * Updates the global WordPress rewrite instance if it is wrong.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return string Either false, or the original string for later restore.
	 */
	private function fix_term_base( string $taxonomy ): string {

		$expected = $this->get_expected_base( $taxonomy );

		$existing = $this->wp_rewrite->get_extra_permastruct( $taxonomy ) ?: '';

		if ( ! $this->is_update_required( $expected, $existing ) ) {
			return '';
		}

		$this->set_permastruct( $taxonomy, $expected );

		return $existing;
	}

	/**
	 * Finds a custom taxonomy base.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return string the prepared string or an empty one
	 */
	private function get_expected_base( string $taxonomy ): string {

		$taxonomies = [
			'category' => 'category_base',
			'post_tag' => 'tag_base',
		];
		if ( empty( $taxonomies[ $taxonomy ] ) ) {
			return '';
		}

		$option = get_option( $taxonomies[ $taxonomy ] );
		if ( ! $option ) {
			return '';
		}

		return $option . '/%' . $taxonomy . '%';
	}

	/**
	 * Checks if the given taxonomy bases require an update.
	 *
	 * @param string $expected Expected taxonomy base.
	 * @param string $existing Existing taxonomy base.
	 *
	 * @return bool Whether or not the taxonomy bases require an update.
	 */
	private function is_update_required( string $expected, string $existing ): bool {

		return $expected && $existing && $existing !== $expected;
	}

	/**
	 * Updates the global WordPress rewrite instance for the given custom taxonomy.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @param string $struct   Rewrite struct.
	 *
	 * @return void
	 */
	private function set_permastruct( string $taxonomy, string $struct ) {

		$this->wp_rewrite->extra_permastructs[ $taxonomy ]['struct'] = $struct;
	}
}
