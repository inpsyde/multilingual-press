<?php

/**
 * Mlp_Term_Connector
 *
 * @version 2015.07.06
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
class Mlp_Term_Connector {

	/**
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce;

	/**
	 * @var string
	 */
	private $taxonomies;

	/**
	 * @var Mlp_Content_Relations_Interface
	 */
	private $content_relations;

	/**
	 * @var int
	 */
	private $current_site_id;

	/**
	 * @var array
	 */
	private $post_data;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param Mlp_Content_Relations_Interface   $content_relations Content relations object.
	 * @param Inpsyde_Nonce_Validator_Interface $nonce             Nonce validator object.
	 * @param array                             $taxonomies        Taxonomy names.
	 * @param array                             $post_data         Post data.
	 */
	public function __construct(
		Mlp_Content_Relations_Interface $content_relations,
		Inpsyde_Nonce_Validator_Interface $nonce,
		array $taxonomies,
		array $post_data
	) {

		$this->nonce = $nonce;
		$this->taxonomies = $taxonomies;
		$this->content_relations = $content_relations;
		$this->current_site_id = get_current_blog_id();
		$this->post_data = $post_data;
	}

	/**
	 * Handle term changes.
	 *
	 * @wp-hook create_term
	 * @wp-hook delete_term
	 * @wp-hook edit_term
	 *
	 * @param int    $term_id          Term ID. Not used.
	 * @param int    $term_taxonomy_id Term taxonomy ID.
	 * @param string $taxonomy         Taxonomy slug.
	 *
	 * @return bool
	 */
	public function change_term_relationships(
		/** @noinspection PhpUnusedParameterInspection */
		$term_id, $term_taxonomy_id, $taxonomy
	) {

		if ( ! in_array( $taxonomy, $this->taxonomies, true ) ) {
			return false;
		}

		/**
		 * This is a core bug!
		 *
		 * @see https://core.trac.wordpress.org/ticket/32876
		 */
		$term_taxonomy_id = (int) $term_taxonomy_id;

		$success = FALSE;

		$current_filter = current_filter();

		if ( is_callable( array( $this, $current_filter ) ) ) {
			/**
			 * Runs before the terms are changed.
			 *
			 * @param int    $term_taxonomy_id Term taxonomy ID.
			 * @param string $taxonomy         Taxonomy name.
			 * @param string $current_filter   Current filter.
			 */
			do_action(
				'mlp_before_term_synchronization',
				$term_taxonomy_id,
				$taxonomy,
				$current_filter
			);

			$success = call_user_func( array( $this, $current_filter ), $term_taxonomy_id );

			/**
			 * Runs after the terms have been changed.
			 *
			 * @param int    $term_taxonomy_id Term taxonomy ID.
			 * @param string $taxonomy         Taxonomy name.
			 * @param string $current_filter   Current filter.
			 * @param bool   $success          Denotes whether or not the database was changed.
			 */
			do_action(
				'mlp_after_term_synchronization',
				$term_taxonomy_id,
				$taxonomy,
				$current_filter,
				$success
			);
		}

		return $success;
	}

	/**
	 * Handle term creation.
	 *
	 * @param int $term_taxonomy_id Term taxonomy ID.
	 *
	 * @return bool
	 */
	public function create_term( $term_taxonomy_id ) {

		if ( ! $this->nonce->is_valid() ) {
			return FALSE;
		}

		$success = FALSE;

		foreach ( $this->post_data as $target_site_id => $target_term_taxonomy_id ) {
			$target_term_taxonomy_id = (int) $target_term_taxonomy_id;

			// There's nothing to do here
			if ( -1 === $target_term_taxonomy_id ) {
				continue;
			}

			$translation_ids = $this->content_relations->get_translation_ids(
				$this->current_site_id,
				$target_site_id,
				$term_taxonomy_id,
				$target_term_taxonomy_id,
				'term'
			);

			if ( $translation_ids[ 'ml_source_blogid' ] !== $this->current_site_id ) {
				$target_site_id = $this->current_site_id;
				$target_term_taxonomy_id = $term_taxonomy_id;
			}

			$result = $this->content_relations->set_relation(
				$translation_ids[ 'ml_source_blogid' ],
				$target_site_id,
				$translation_ids[ 'ml_source_elementid' ],
				$target_term_taxonomy_id,
				'term'
			);
			if ( $result ) {
				$success = TRUE;
			}
		}

		return $success;
	}

	/**
	 * Handle term deletion.
	 *
	 * @param int $term_taxonomy_id Term taxonomy ID.
	 *
	 * @return bool
	 */
	public function delete_term( $term_taxonomy_id ) {

		$translation_ids = $this->content_relations->get_translation_ids(
			$this->current_site_id,
			0,
			$term_taxonomy_id,
			0,
			'term'
		);

		$relations = $this->content_relations->get_relations(
			$translation_ids['ml_source_blogid'],
			$translation_ids['ml_source_elementid'],
			'term'
		);

		$target_site_id = ( 2 < count( $relations ) ) ? $this->current_site_id : 0;

		return $this->content_relations->delete_relation(
			$translation_ids['ml_source_blogid'],
			$target_site_id,
			$translation_ids['ml_source_elementid'],
			0,
			'term'
		);
	}

	/**
	 * Handle term edits.
	 *
	 * @param int $term_taxonomy_id Term taxonomy ID.
	 *
	 * @return bool
	 */
	public function edit_term( $term_taxonomy_id ) {

		if ( ! $this->nonce->is_valid() ) {
			return FALSE;
		}

		$success = FALSE;

		$existing = $this->content_relations->get_relations(
			$this->current_site_id,
			$term_taxonomy_id,
			'term'
		);

		foreach ( $this->post_data as $target_site_id => $target_term_taxonomy_id ) {
			$result = $this->update_terms(
				$existing,
				$term_taxonomy_id,
				$target_site_id,
				(int) $target_term_taxonomy_id
			);
			if ( $result ) {
				$success = TRUE;
			}
		}

		return $success;
	}

	/**
	 * @param array $existing
	 * @param int   $source_term_taxonomy_id
	 * @param int   $target_site_id
	 * @param int   $target_term_taxonomy_id
	 *
	 * @return bool
	 */
	private function update_terms(
		array $existing,
		$source_term_taxonomy_id,
		$target_site_id,
		$target_term_taxonomy_id
	) {

		// There's nothing to do here
		if ( -1 === $target_term_taxonomy_id ) {
			return TRUE;
		}

		if (
			isset( $existing[ $target_site_id ] )
			&& $existing[ $target_site_id ] === $target_term_taxonomy_id
		) {
			return TRUE;
		}

		$translation_ids = $this->content_relations->get_translation_ids(
			$this->current_site_id,
			$target_site_id,
			$source_term_taxonomy_id,
			$target_term_taxonomy_id,
			'term'
		);

		if ( $translation_ids[ 'ml_source_blogid' ] !== $this->current_site_id ) {
			$target_site_id = $this->current_site_id;
			if ( 0 !== $target_term_taxonomy_id ) {
				$target_term_taxonomy_id = $source_term_taxonomy_id;
			}
		}

		// Delete a relation
		if ( 0 === $target_term_taxonomy_id ) {
			return $this->content_relations->delete_relation(
				$translation_ids[ 'ml_source_blogid' ],
				$target_site_id,
				$translation_ids[ 'ml_source_elementid' ],
				0,
				'term'
			);
		}

		return $this->content_relations->set_relation(
			$translation_ids[ 'ml_source_blogid' ],
			$target_site_id,
			$translation_ids[ 'ml_source_elementid' ],
			$target_term_taxonomy_id,
			'term'
		);
	}
}
