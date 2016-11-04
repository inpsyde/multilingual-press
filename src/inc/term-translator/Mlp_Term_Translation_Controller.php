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
	private $view = NULL;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var Mlp_Term_Translation_Presenter
	 */
	private $presenter;

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

	/**
	 * @return bool
	 */
	public function setup() {

		$taxonomies = $this->get_valid_taxonomies();
		if ( empty( $taxonomies ) ) {
			return FALSE;
		}

		$fields = new Mlp_Term_Fields( $taxonomies, $this );
		add_action( 'load-edit-tags.php', [ $fields, 'setup' ] );

		$post_data = $this->get_post_data();

		$delete = isset( $_POST[ 'action' ] ) && 'delete-tag' === $_POST[ 'action' ];

		if ( $post_data ) {
			$this->activate_switcher();
		}

		if ( $post_data || $delete ) {
			$this->activate_term_connector( $taxonomies, $post_data, $delete );

			return TRUE;
		}

		return FALSE;
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

		if ( ! is_null( $this->view ) ) {
			return $this->view;
		}

		$this->presenter = new Mlp_Term_Translation_Presenter(
			$this->content_relations,
			$this->key_base
		);

		$this->view = new Mlp_Term_Translation_Selector( $this->nonce, $this->presenter );

		return $this->view;
	}

	/**
	 * @return array
	 */
	private function get_valid_taxonomies() {

		/** This filter is documented in inc/post-translator/Mlp_Translation_Metabox.php */
		$post_types = (array) apply_filters( 'mlp_allowed_post_types', [ 'post', 'page' ] );
		if ( empty( $post_types ) ) {
			return [];
		}

		return get_object_taxonomies( $post_types );
	}

	/**
	 * @return void
	 */
	private function activate_switcher() {

		$switcher = new Mlp_Global_Switcher( Mlp_Global_Switcher::TYPE_POST );

		add_action(
			'mlp_before_term_synchronization',
			[ $switcher, 'strip' ]
		);
		add_action(
			'mlp_after_term_synchronization',
			[ $switcher, 'fill' ]
		);
	}

	/**
	 * @param array $taxonomies
	 * @param array $post_data
	 * @param bool  $delete
	 *
	 * @return void
	 */
	private function activate_term_connector( array $taxonomies, array $post_data, $delete ) {

		$connector = new Mlp_Term_Connector(
			$this->content_relations,
			$this->nonce,
			$taxonomies,
			$post_data
		);

		$actions = $delete ? [ 'delete' ] : [ 'create', 'edit' ];

		foreach ( $actions as $action ) {
			add_action(
				"{$action}_term",
				[ $connector, 'change_term_relationships' ],
				10,
				3
			);
		}
	}

}
