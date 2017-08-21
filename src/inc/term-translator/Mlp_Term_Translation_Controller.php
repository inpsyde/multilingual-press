<?php

use Inpsyde\MultilingualPress\API\ContentRelations;

class Mlp_Term_Translation_Controller {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @param ContentRelations $content_relations
	 */
	public function __construct( ContentRelations $content_relations ) {

		$this->content_relations = $content_relations;
	}

	public function setup() {

		$post_data = 'POST' === $_SERVER['REQUEST_METHOD']
			? (array) ( $_POST['mlp']['term_translation'] ?? [] )
			: [];

		$delete = isset( $_POST['action'] ) && 'delete-tag' === $_POST['action'];

		if ( $post_data || $delete ) {
			$connector = new Mlp_Term_Connector( $this->content_relations, $post_data );

			foreach ( $delete ? [ 'delete' ] : [ 'create', 'edit' ] as $action ) {
				add_action( "{$action}_term", [ $connector, 'change_term_relationships' ], 10, 3 );
			}
		}
	}
}
