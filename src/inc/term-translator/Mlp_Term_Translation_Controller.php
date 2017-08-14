<?php

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

/**
 * Mlp_Term_Translation_Controller
 *
 * @version 2015.08.21
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
class Mlp_Term_Translation_Controller implements Mlp_Updatable {

	/**
	 * @var Mlp_Term_Translation_Selector
	 */
	private $view;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var string
	 */
	private $key_base = 'mlp[term_translation]';

	/**
	 * @param ContentRelations $content_relations
	 * @param Nonce            $nonce             Nonce object.
	 */
	public function __construct( ContentRelations $content_relations, Nonce $nonce ) {

		$this->content_relations = $content_relations;

		$this->nonce = $nonce;
	}

	public function setup() {

		$post_data = $this->get_post_data();

		$delete = isset( $_POST[ 'action' ] ) && 'delete-tag' === $_POST[ 'action' ];

		if ( $post_data || $delete ) {
			$connector = new Mlp_Term_Connector(
				$this->content_relations,
				$this->nonce,
				[],
				$post_data
			);

			foreach ( $delete ? [ 'delete' ] : [ 'create', 'edit' ] as $action ) {
				add_action(
					"{$action}_term",
					[ $connector, 'change_term_relationships' ],
					10,
					3
				);
			}
		}
	}

	/**
	 * @return array
	 */
	private function get_post_data() {

		if ( 'POST' !== $_SERVER[ 'REQUEST_METHOD' ] ) {
			return [];
		}

		if ( empty( $_POST[ 'mlp' ][ 'term_translation' ] ) ) {
			return [];
		}

		return (array) $_POST[ 'mlp' ][ 'term_translation' ];
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|void Either a value, or void for actions.
	 */
	public function update( $name ) {

		$view = $this->get_view();

		if ( Mlp_Term_Field_View::ADD_TERM_FIELDSET_ID === $name ) {
			return $view->get_fieldset_id();
		}

		$table_positions = [
			Mlp_Term_Field_View::ADD_TERM_FIELDS,
			Mlp_Term_Field_View::EDIT_TERM_FIELDS,
		 ];
		if ( in_array( $name, $table_positions, true ) ) {
			return $view->print_table();
		}

		$title_positions = [
			Mlp_Term_Field_View::ADD_TERM_TITLE,
			Mlp_Term_Field_View::EDIT_TERM_TITLE,
		 ];
		if ( in_array( $name, $title_positions, true ) ) {
			return $view->get_title();
		}

		return FALSE;
	}

	/**
	 * @return Mlp_Term_Translation_Selector
	 */
	private function get_view() {

		if ( isset( $this->view ) ) {
			return $this->view;
		}

		$this->view = new Mlp_Term_Translation_Selector(
			$this->nonce,
			new Mlp_Term_Translation_Presenter(
				$this->content_relations,
				$this->key_base
			)
		);

		return $this->view;
	}
}
