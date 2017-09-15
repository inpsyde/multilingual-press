<?php # -*- coding: utf-8 -*-

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
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce;

	/**
	 * @var string
	 */
	private $key_base;

	/**
	 * @var Mlp_Content_Relations_Interface
	 */
	private $content_relations;

	/**
	 * @var array
	 */
	private $site_terms = array();

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param Mlp_Content_Relations_Interface   $content_relations Content relations object.
	 * @param Inpsyde_Nonce_Validator_Interface $nonce             Nonce validator object.
	 * @param string                            $key_base          Term key base.
	 */
	public function __construct(
		Mlp_Content_Relations_Interface $content_relations,
		Inpsyde_Nonce_Validator_Interface $nonce,
		$key_base
	) {

		$this->content_relations = $content_relations;
		$this->nonce = $nonce;
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

		$out = array();

		switch_to_blog( $site_id );

		$taxonomy_object = get_taxonomy( $this->taxonomy_name );

		if ( ! current_user_can( $taxonomy_object->cap->edit_terms ) ) {
			$terms = array();
		} else {
			$terms = get_terms( $this->taxonomy_name, array(
				'hide_empty' => false,
			) );
		}

		foreach ( $terms as $term ) {
			if ( is_taxonomy_hierarchical( $this->taxonomy_name ) ) {
				$ancestors = get_ancestors( $term->term_id, $this->taxonomy_name );
				if ( ! empty( $ancestors ) ) {
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

		$languages = mlp_get_available_languages_titles();

		$current_blog_id = get_current_blog_id();
		unset( $languages[ $current_blog_id ] );

		return $languages;
	}

	/**
	 * Return the nonce field.
	 *
	 * @return void
	 */
	public function print_nonce_field() {

		wp_nonce_field( $this->nonce->get_action(), $this->nonce->get_name() );
	}

	/**
	 * Return the group title.
	 *
	 * @return string
	 */
	public function get_group_title() {

		return __( 'Translations', 'multilingual-press' );
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

		return $relation['ml_source_blogid'] . '-' . $relation['ml_source_elementid'];
	}

}
