<?php
/**
 * Mlp_Term_Translation_Controller
 *
 * @version 2014.09.19
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Term_Translation_Controller implements Mlp_Updatable {

	/**
	 * @type Mlp_Term_Translation_Selector
	 */
	private $view = NULL;

	/**
	 * @type Inpsyde_Nonce_Validator
	 */
	private $nonce;

	/**
	 * @type Mlp_Term_Translation_Presenter
	 */
	private $presenter;

	/**
	 * @type Mlp_Content_Relations_Interface
	 */
	private $content_relations;

	/**
	 * @type string
	 */
	private $key_base = 'mlp[term_translation]';

	/**
	 * @param Mlp_Content_Relations_Interface $content_relations
	 */
	public function __construct( Mlp_Content_Relations_Interface $content_relations ) {

		$this->content_relations = $content_relations;
		$current_site            = get_current_blog_id();
		$this->nonce             = new Inpsyde_Nonce_Validator(
			'mlp_term_translation',
			$current_site
		);

	}

	/**
	 * @wp-hook load-edit-tags.php
	 * @return bool
	 */
	public function setup() {

		$taxonomies = $this->get_valid_taxonomies();

		if ( empty ( $taxonomies ) )
			return FALSE;

		$fields = new Mlp_Term_Fields( $taxonomies, $this );
		add_action( 'load-edit-tags.php', array ( $fields, 'setup' ) );

		$post_data = $this->get_post_data();

		if ( empty ( $post_data ) )
			return TRUE;

		$this->activate_switcher();
		$this->activate_term_connector( $taxonomies, $post_data );

		return TRUE;
	}

	/**
	 * @return array
	 */
	private function get_post_data() {

		if ( 'POST' !== $_SERVER[ 'REQUEST_METHOD' ] )
			return array();

		if ( empty ( $_POST[ 'mlp' ] ) )
			return array();

		if ( empty ( $_POST[ 'mlp' ][ 'term_translation' ] ) )
			return array();

		return $_POST[ 'mlp' ][ 'term_translation' ];
	}

	/**
	 * Called by the generic view Mlp_Term_Field_View.
	 *
	 * @param  string $name
	 * @return bool  TRUE when an action was called, FALSE for an unrecognized $name.
	 */
	public function update( $name ) {

		$view = $this->get_view();

		if ( Mlp_Term_Field_View::ADD_TERM_FIELDSET_ID === $name )
			return $view->print_fieldset_id();

		$table_positions = array (
			Mlp_Term_Field_View::ADD_TERM_FIELDS,
			Mlp_Term_Field_View::EDIT_TERM_FIELDS
		);

		if ( in_array( $name, $table_positions ) )
			return $view->print_table();

		$title_positions = array (
			Mlp_Term_Field_View::ADD_TERM_TITLE,
			Mlp_Term_Field_View::EDIT_TERM_TITLE
		);

		if ( in_array( $name, $title_positions ) )
			return $view->print_title();

		return FALSE;
	}

	/**
	 * @return Mlp_Term_Translation_Selector
	 */
	private function get_view() {

		if ( ! is_null( $this->view ) )
			return $this->view;

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

		$post_types = array (
			'post',
			'page'
		);
		$post_types = (array) apply_filters( 'mlp_allowed_post_types', $post_types );
		$taxonomies = get_object_taxonomies( $post_types );

		return $taxonomies;
	}

	/**
	 * @return void
	 */
	private function activate_switcher() {

		$switcher = new Mlp_Global_Switcher( Mlp_Global_Switcher::TYPE_POST );

		add_action(
			'mlp_before_term_synchronization',
			array ( $switcher, 'strip' )
		);
		add_action(
			'mlp_after_term_synchronization',
			array ( $switcher, 'fill' )
		);
	}

	/**
	 * @param array $taxonomies
	 * @param array $post_data
	 * @return void
	 */
	private function activate_term_connector( Array $taxonomies, Array $post_data ) {

		$connector = new Mlp_Term_Connector(
			$this->content_relations,
			$this->nonce,
			$taxonomies,
			$post_data
		);

		$actions = array ( 'create', 'delete', 'edit' );

		foreach ( $actions as $action ) {
			add_action(
				"{$action}_term",
				array ( $connector, 'change_term_relationships' ),
				10,
				4
			);
		}
	}
}