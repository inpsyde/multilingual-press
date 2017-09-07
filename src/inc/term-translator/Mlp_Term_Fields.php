<?php # -*- coding: utf-8 -*-

/**
 * Generic controller to add fields to the term edit/add screens.
 *
 * @version 2015.06.29
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Term_Fields {

	/**
	 * @var array
	 */
	private $taxonomies;

	/**
	 * @var Mlp_Updatable
	 */
	private $updatable;

	/**
	 * @param array         $taxonomies
	 * @param Mlp_Updatable $updatable
	 */
	public function __construct( array $taxonomies, Mlp_Updatable $updatable ) {

		$this->taxonomies = $taxonomies;
		$this->updatable = $updatable;
	}

	/**
	 * @return bool
	 */
	public function setup() {

		$taxonomy = $this->get_current_taxonomy();
		if ( '' === $taxonomy ) {
			return false;
		}

		$view = new Mlp_Term_Field_View( $this->updatable );

		add_action( "{$taxonomy}_add_form_fields", array( $view, 'add_term' ) );
		add_action( "{$taxonomy}_edit_form_fields", array( $view, 'edit_term' ) );

		return true;
	}

	/**
	 * @return string
	 */
	private function get_current_taxonomy() {

		$screen = get_current_screen();

		if ( empty( $screen->taxonomy ) ) {
			return '';
		}

		if ( ! in_array( $screen->taxonomy, $this->taxonomies, true ) ) {
			return '';
		}

		return $screen->taxonomy;
	}

}
