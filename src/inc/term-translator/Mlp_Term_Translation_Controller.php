<?php

/**
 * Mlp_Term_Translation_Controller
 *
 * @version 2015.08.21
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
class Mlp_Term_Translation_Controller implements Mlp_Updatable {

	/**
	 * @var Mlp_Assets_Interface
	 */
	private $assets;

	/**
	 * @var Mlp_Term_Translation_Selector
	 */
	private $view = null;

	/**
	 * @var Inpsyde_Nonce_Validator
	 */
	private $nonce;

	/**
	 * @var Mlp_Term_Translation_Presenter
	 */
	private $presenter;

	/**
	 * @var Mlp_Content_Relations_Interface
	 */
	private $content_relations;

	/**
	 * @var string
	 */
	private $key_base = 'mlp[term_translation]';

	/**
	 * @param Mlp_Content_Relations_Interface $content_relations
	 * @param Mlp_Assets_Interface            $assets
	 */
	public function __construct( Mlp_Content_Relations_Interface $content_relations, Mlp_Assets_Interface $assets ) {

		$this->content_relations = $content_relations;

		$this->assets = $assets;

		$this->nonce = Mlp_Nonce_Validator_Factory::create( $this->get_nonce_action(), get_current_blog_id() );
	}

	/**
	 * Returns the nonce action for the current request wrt. the current taxonomy and term, if any.
	 *
	 * @return string
	 */
	private function get_nonce_action() {

		$is_post_request = 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] );
		if ( $is_post_request ) {
			$taxonomy = filter_input( INPUT_POST, 'taxonomy' );

			$term_taxonomy_id = filter_input( INPUT_POST, 'tag_ID' );
		}

		if ( ! isset( $taxonomy ) ) {
			$taxonomy = (string) filter_input( INPUT_GET, 'taxonomy' );
		}

		if ( ! isset( $term_taxonomy_id ) ) {
			$term_taxonomy_id = (int) filter_input( INPUT_GET, 'tag_ID' );
		}

		$action = "save_{$taxonomy}_translations_$term_taxonomy_id";

		return $action;
	}

	/**
	 * @return bool
	 */
	public function setup() {

		$taxonomies = $this->get_valid_taxonomies();
		if ( empty( $taxonomies ) ) {
			return false;
		}

		$fields = new Mlp_Term_Fields( $taxonomies, $this );
		add_action( 'load-edit-tags.php', array( $fields, 'setup' ) );

		add_action( 'load-edit-tags.php', array( $this, 'provide_assets' ) );

		$post_data = $this->get_post_data();

		$delete = 'delete-tag' === filter_input( INPUT_POST, 'action' );

		if ( $post_data ) {
			$this->activate_switcher();
		}

		if ( $post_data || $delete ) {
			$this->activate_term_connector( $taxonomies, $post_data, $delete );

			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	private function get_post_data() {

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return array();
		}

		$data = filter_input_array( INPUT_POST, FILTER_DEFAULT, false );
		if ( empty( $data['mlp']['term_translation'] ) ) {
			return array();
		}

		return (array) $data['mlp']['term_translation'];
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

		$table_positions = array(
			Mlp_Term_Field_View::ADD_TERM_FIELDS,
			Mlp_Term_Field_View::EDIT_TERM_FIELDS,
		);
		if ( in_array( $name, $table_positions, true ) ) {
			return $view->print_table();
		}

		$title_positions = array(
			Mlp_Term_Field_View::ADD_TERM_TITLE,
			Mlp_Term_Field_View::EDIT_TERM_TITLE,
		);
		if ( in_array( $name, $title_positions, true ) ) {
			return $view->get_title();
		}

		return false;
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
			$this->nonce,
			$this->key_base
		);

		$this->view = new Mlp_Term_Translation_Selector( $this->presenter );

		return $this->view;
	}

	/**
	 * @return array
	 */
	private function get_valid_taxonomies() {

		/** This filter is documented in inc/post-translator/Mlp_Translation_Metabox.php */
		$post_types = (array) apply_filters( 'mlp_allowed_post_types', array( 'post', 'page' ) );
		if ( empty( $post_types ) ) {
			return array();
		}

		/**
		 * Filters the allowed taxonomies.
		 *
		 * @since 2.9.0
		 *
		 * @param string[] $active_taxonomies Allowed taxonomy names.
		 */
		return (array) apply_filters( 'multilingualpress.active_taxonomies', get_object_taxonomies( $post_types ) );
	}

	/**
	 * @return void
	 */
	private function activate_switcher() {

		$switcher = new Mlp_Global_Switcher( Mlp_Global_Switcher::TYPE_POST );

		add_action(
			'mlp_before_term_synchronization',
			array( $switcher, 'strip' )
		);
		add_action(
			'mlp_after_term_synchronization',
			array( $switcher, 'fill' )
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

		$actions = $delete ? array( 'delete' ) : array( 'create', 'edit' );

		foreach ( $actions as $action ) {
			add_action(
				"{$action}_term",
				array( $connector, 'change_term_relationships' ),
				10,
				3
			);
		}
	}

	/**
	 * Takes care of the required assets being provided.
	 *
	 * @return void
	 */
	public function provide_assets() {

		$this->assets->provide( 'mlp-admin' );
	}
}
