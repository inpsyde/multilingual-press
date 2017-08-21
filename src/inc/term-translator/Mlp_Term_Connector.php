<?php

use Inpsyde\MultilingualPress\API\ContentRelations;

class Mlp_Term_Connector {

	/**
	 * @var ContentRelations
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
	 * @param ContentRelations $content_relations
	 * @param array            $post_data
	 */
	public function __construct( ContentRelations $content_relations, array $post_data ) {

		$this->content_relations = $content_relations;

		$this->post_data = $post_data;

		$this->current_site_id = get_current_blog_id();
	}

	/**
	 * @param int    $term_id
	 * @param int    $term_taxonomy_id
	 * @param string $taxonomy
	 * @return bool
	 */
	public function change_term_relationships(
		/* @noinspection PhpUnusedParameterInspection */
		$term_id, $term_taxonomy_id, $taxonomy
	) {

		$current_filter = current_filter();

		if ( is_callable( [ $this, $current_filter ] ) ) {
			return call_user_func( [ $this, $current_filter ], (int) $term_taxonomy_id );
		}

		return false;
	}

	/**
	 * @param int $term_taxonomy_id
	 * @return bool
	 */
	public function create_term( $term_taxonomy_id ) {

		$success = false;

		foreach ( $this->post_data as $target_site_id => $target_term_taxonomy_id ) {
			$target_term_taxonomy_id = (int) $target_term_taxonomy_id;

			if ( - 1 === $target_term_taxonomy_id ) {
				continue;
			}

			$translation_ids = $this->content_relations->get_translation_ids(
				$this->current_site_id,
				$target_site_id,
				$term_taxonomy_id,
				$target_term_taxonomy_id,
				'term'
			);

			if ( $translation_ids['ml_source_blogid'] !== $this->current_site_id ) {
				$target_site_id = $this->current_site_id;

				$target_term_taxonomy_id = $term_taxonomy_id;
			}

			if ( $this->content_relations->set_relation(
				$translation_ids['ml_source_blogid'],
				$target_site_id,
				$translation_ids['ml_source_elementid'],
				$target_term_taxonomy_id,
				'term'
			) ) {
				$success = true;
			}
		}

		return $success;
	}

	/**
	 * @param int $term_taxonomy_id
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
	 * @param int $term_taxonomy_id
	 * @return bool
	 */
	public function edit_term( $term_taxonomy_id ) {

		$success = false;

		$existing = $this->content_relations->get_relations(
			$this->current_site_id,
			$term_taxonomy_id,
			'term'
		);

		foreach ( $this->post_data as $target_site_id => $target_term_taxonomy_id ) {
			if ( $this->update_terms(
				$existing,
				$term_taxonomy_id,
				$target_site_id,
				(int) $target_term_taxonomy_id
			) ) {
				$success = true;
			}
		}

		return $success;
	}

	/**
	 * @param array $existing
	 * @param int   $source_term_taxonomy_id
	 * @param int   $target_site_id
	 * @param int   $target_term_taxonomy_id
	 * @return bool
	 */
	private function update_terms(
		array $existing,
		$source_term_taxonomy_id,
		$target_site_id,
		$target_term_taxonomy_id
	) {

		if ( - 1 === $target_term_taxonomy_id ) {
			return true;
		}

		if (
			isset( $existing[ $target_site_id ] )
			&& $existing[ $target_site_id ] === $target_term_taxonomy_id
		) {
			return true;
		}

		$translation_ids = $this->content_relations->get_translation_ids(
			$this->current_site_id,
			$target_site_id,
			$source_term_taxonomy_id,
			$target_term_taxonomy_id,
			'term'
		);

		if ( $translation_ids['ml_source_blogid'] !== $this->current_site_id ) {
			$target_site_id = $this->current_site_id;
			if ( 0 !== $target_term_taxonomy_id ) {
				$target_term_taxonomy_id = $source_term_taxonomy_id;
			}
		}

		// Delete a relation
		if ( 0 === $target_term_taxonomy_id ) {
			return $this->content_relations->delete_relation(
				$translation_ids['ml_source_blogid'],
				$target_site_id,
				$translation_ids['ml_source_elementid'],
				0,
				'term'
			);
		}

		return $this->content_relations->set_relation(
			$translation_ids['ml_source_blogid'],
			$target_site_id,
			$translation_ids['ml_source_elementid'],
			$target_term_taxonomy_id,
			'term'
		);
	}
}
