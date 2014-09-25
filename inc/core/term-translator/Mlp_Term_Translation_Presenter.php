<?php # -*- coding: utf-8 -*-
/**
 * Prepare data for the term edit form.
 *
 * @version 2014.09.18
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Term_Translation_Presenter {

	/**
	 * @var string
	 */
	private $taxonomy;

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
	 * @param Mlp_Content_Relations_Interface   $content_relations
	 * @param Inpsyde_Nonce_Validator_Interface $nonce
	 * @param string                            $key_base
	 */
	public function __construct(
		Mlp_Content_Relations_Interface   $content_relations,
		Inpsyde_Nonce_Validator_Interface $nonce,
		$key_base
	) {

		$this->taxonomy          = get_current_screen()->taxonomy;
		$this->nonce             = $nonce;
		$this->key_base          = $key_base;
		$this->content_relations = $content_relations;
		$this->current_site_id   = get_current_blog_id();
	}

	/**
	 * @param  int $site_id
	 * @return string
	 */
	public function get_key_base( $site_id ) {

		return "$this->key_base[$site_id]";
	}

	/**
	 * @param  int $site_id
	 * @return array
	 */
	public function get_terms_for_site( $site_id ) {

		switch_to_blog( $site_id );

		$terms = get_terms( $this->taxonomy, array ( 'hide_empty' => FALSE ) );

		restore_current_blog();

		if ( empty ( $terms ) || is_wp_error( $terms ) )
			return array();

		$out = array();

		foreach ( $terms as $term )
			$out[ $term->term_taxonomy_id ] = esc_html( $term->name );

		return $out;
	}

	/**
	 * @return string
	 */
	public function get_taxonomy() {

		return $this->taxonomy;
	}

	/**
	 * @return array
	 */
	public function get_site_languages() {

		$languages = mlp_get_available_languages_titles();
		unset ( $languages[ get_current_blog_id() ] );

		return $languages;
	}

	/**
	 * @return string
	 */
	public function get_nonce_field() {

		return wp_nonce_field(
			$this->nonce->get_action(),
			$this->nonce->get_name(),
			TRUE ,
			FALSE
		);
	}

	/**
	 * @return string
	 */
	public function get_group_title() {

		return esc_html__( 'Translations', 'multilingualpress' );
	}

	/**
	 * @param  int $site_id
	 * @param  int $term_id // currently edited term
	 * @return int
	 */
	public function get_current_term( $site_id, $term_id ) {

		if ( ! isset ( $this->site_terms[ $term_id ] ) ) {
			$this->site_terms[ $term_id ] = $this->content_relations->get_relations(
				$this->current_site_id,
				$term_id,
				'term'
			);
		}

		if ( empty ( $this->site_terms[ $term_id ][ $site_id ] ) )
			return 0;

		return $this->site_terms[ $term_id ][ $site_id ];
	}
}