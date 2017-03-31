<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\ContentRelations;

use function Inpsyde\MultilingualPress\get_available_language_names;

/**
 * Prepare data for the term edit form.
 *
 * @version 2015.07.06
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
class Mlp_Term_Translation_Presenter {

	/**
	 * @var string
	 */
	private $taxonomy_name;

	/**
	 * @var string
	 */
	private $key_base;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var array
	 */
	private $site_terms = [];

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param ContentRelations $content_relations Content relations object.
	 * @param string           $key_base          Term key base.
	 */
	public function __construct(
		ContentRelations $content_relations,
		$key_base
	) {

		$this->content_relations = $content_relations;
		$this->key_base = $key_base;

		$this->current_site_id = get_current_blog_id();

		$current_screen = get_current_screen();
		$this->taxonomy_name = $current_screen->taxonomy;
	}

	/**
	 * Term key base for given site.
	 *
	 * @param int $site_id Blog ID.
	 *
	 * @return string
	 */
	public function get_key_base( $site_id ) {

		return "{$this->key_base}[$site_id]";
	}

	/**
	 * Return the terms for the given type.
	 *
	 * @param int $site_id Blog ID.
	 *
	 * @return array
	 */
	public function get_terms_for_site( $site_id ) {

		$out = [];

		switch_to_blog( $site_id );

		$taxonomy_object = get_taxonomy( $this->taxonomy_name );

		if ( ! current_user_can( $taxonomy_object->cap->edit_terms ) ) {
			$terms = [];
		} else {
			$terms = get_terms( $this->taxonomy_name, [ 'hide_empty' => FALSE ] );
		}

		foreach ( $terms as $term ) {
			if ( is_taxonomy_hierarchical( $this->taxonomy_name ) ) {
				$ancestors = get_ancestors( $term->term_id, $this->taxonomy_name );
				if ( ! empty ( $ancestors ) ) {
					foreach ( $ancestors as $ancestor ) {
						$parent_term = get_term( $ancestor, $this->taxonomy_name );
						$term->name = $parent_term->name . '/' . $term->name;
					}
				}
			}
			$out[ $term->term_taxonomy_id ] = esc_html( $term->name );
		}

		restore_current_blog();

		uasort( $out, 'strcasecmp' );

		return $out;
	}

	/**
	 * Return the current taxonomy name.
	 *
	 * @return string
	 */
	public function get_taxonomy() {

		return $this->taxonomy_name;
	}

	/**
	 * Return the available site languages.
	 *
	 * @return array
	 */
	public function get_site_languages() {

		$languages = get_available_language_names();
		unset( $languages[ get_current_blog_id() ] );

		return $languages;
	}

	/**
	 * Return the group title.
	 *
	 * @return string
	 */
	public function get_group_title() {

		return esc_html__( 'Translations', 'multilingualpress' );
	}

	/**
	 * Return the current term taxonomy ID for the given site and the given term ID in the current site.
	 *
	 * @param int $site_id Blog ID.
	 * @param int $term_id Term ID of the currently edited term.
	 *
	 * @return int
	 */
	public function get_current_term( $site_id, $term_id ) {

		$term = $this->get_term_from_site( $term_id );
		if ( ! isset( $term->term_taxonomy_id ) ) {
			return 0;
		}

		if ( ! isset( $this->site_terms[ $term->term_taxonomy_id ][ $site_id ] ) ) {
			$term_taxonomy_id = $this->content_relations->get_element_for_site(
				$this->current_site_id,
				$site_id,
				$term->term_taxonomy_id,
				'term'
			);
			if ( $term_taxonomy_id ) {
				$this->site_terms[ $term->term_taxonomy_id ][ $site_id ] = $term_taxonomy_id;
			}
		}

		if ( empty( $this->site_terms[ $term->term_taxonomy_id ][ $site_id ] ) ) {
			return 0;
		}

		return $this->site_terms[ $term->term_taxonomy_id ][ $site_id ];
	}

	/**
	 * Return the term object for the given term ID and the current site.
	 *
	 * @param int $term_id Term ID.
	 *
	 * @return object
	 */
	private function get_term_from_site( $term_id ) {

		switch_to_blog( $this->current_site_id );

		$term = get_term_by( 'id', $term_id, $this->taxonomy_name );

		restore_current_blog();

		return $term;
	}

	/**
	 * Return the relation ID for the given blog ID and term taxonomy ID.
	 *
	 * @param int $site_id          Blog ID.
	 * @param int $term_taxonomy_id Term taxonomy ID.
	 *
	 * @return string
	 */
	public function get_relation_id( $site_id, $term_taxonomy_id ) {

		$translation_ids = $this->content_relations->get_existing_translation_ids(
			$site_id,
			0,
			$term_taxonomy_id,
			0,
			'term'
		);
		if ( ! $translation_ids ) {
			return '';
		}

		$relation = reset( $translation_ids );

		return $relation[ 'ml_source_blogid' ] . '-' . $relation[ 'ml_source_elementid' ];
	}

}
