<?php

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

class Mlp_Term_Translation_Controller {

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @param ContentRelations $content_relations
	 * @param Nonce            $nonce
	 */
	public function __construct( ContentRelations $content_relations, Nonce $nonce ) {

		$this->content_relations = $content_relations;

		$this->nonce = $nonce;
	}

	public function setup() {

		$post_data = 'POST' === $_SERVER['REQUEST_METHOD']
			? (array) ( $_POST['mlp']['term_translation'] ?? [] )
			: [];

		$delete = isset( $_POST['action'] ) && 'delete-tag' === $_POST['action'];

		if ( $post_data || $delete ) {
			$connector = new Mlp_Term_Connector(
				$this->content_relations,
				$this->nonce,
				[],
				$post_data
			);

			foreach ( $delete ? [ 'delete' ] : [ 'create', 'edit' ] as $action ) {
				add_action( "{$action}_term", [ $connector, 'change_term_relationships' ], 10, 3 );
			}
		}
	}
}
