<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\UIAwareMetaBoxRegistrar;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\NetworkState;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxController;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Term\TermMetaBoxView;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Term\TermMetaUpdater;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;
use Inpsyde\MultilingualPress\Relations\Term\RelationshipPermission;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\SourceTermSaveContext;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Meta box registrar implementation for term meta boxes.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term
 * @since   3.0.0
 */
final class TermMetaBoxRegistrar implements UIAwareMetaBoxRegistrar {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_INIT_META_BOXES = 'multilingualpress.init_term_meta_boxes';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_ADD_META_BOXES = 'multilingualpress.add_term_meta_boxes';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_ADDED_META_BOX = 'multilingualpress.added_term_meta_box';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_SAVE_META_BOXES = 'multilingualpress.save_term_meta_boxes';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_SAVED_META_BOXES = 'multilingualpress.saved_term_meta_boxes';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_SAVED_META_BOX_DATA = 'multilingualpress.saved_term_meta_box_data';

	/**
	 * @var MetaBoxFactory
	 */
	private $factory;

	/**
	 * @var NonceFactory
	 */
	private $nonce_factory;

	/**
	 * @var RelationshipPermission
	 */
	private $permission_checker;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * @var MetaBoxUI
	 */
	private $ui;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param MetaBoxFactory         $factory            Meta box factory object.
	 * @param RelationshipPermission $permission_checker Relationship permission checker object.
	 * @param ServerRequest          $request            Request object.
	 * @param NonceFactory           $nonce_factory      Nonce factory object.
	 */
	public function __construct(
		MetaBoxFactory $factory,
		RelationshipPermission $permission_checker,
		ServerRequest $request,
		NonceFactory $nonce_factory
	) {

		$this->factory = $factory;

		$this->permission_checker = $permission_checker;

		$this->server_request = $request;

		$this->nonce_factory = $nonce_factory;
	}

	/**
	 * Returns the ID of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string ID.
	 */
	public function id(): string {

		return __CLASS__;
	}

	/**
	 * Sets the given user interface.
	 *
	 * @since 3.0.0
	 *
	 * @param MetaBoxUI $ui Meta box UI object.
	 *
	 * @return UIAwareMetaBoxRegistrar
	 */
	public function set_ui( MetaBoxUI $ui ): UIAwareMetaBoxRegistrar {

		// Don't allow overwrite
		if ( null !== $this->ui ) {
			throw new \BadMethodCallException( sprintf( 'It is not possible to override UI for %s.', __CLASS__ ) );
		}

		// Don't do anything if called too early
		if ( did_action( self::ACTION_INIT_META_BOXES ) ) {
			$this->ui = $ui;
		}

		return $this;
	}

	/**
	 * Registers meta boxes both for display and updating.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_meta_boxes() {

		add_action( 'current_screen', function ( \WP_Screen $screen ) {

			if ( ! $screen->taxonomy || $screen->id !== "edit-{$screen->taxonomy}" ) {
				return;
			}

			add_action( "{$screen->taxonomy}_edit_form_fields", function ( \WP_Term $term ) {

				$this->add_meta_boxes( $term, true );
			} );

			add_action( "{$screen->taxonomy}_add_form_fields", function ( string $taxonomy ) {

				$this->add_meta_boxes( new \WP_Term( (object) [ 'taxonomy' => $taxonomy, 'term_id' => 0 ] ), false );
			} );
		} );

		// There are 2 different actions for saving terms

		add_action( 'edit_term', function ( $term_id, $tt_id, $taxonomy ) {

			$term = get_term_by( 'term_taxonomy_id', $tt_id );
			if ( $term instanceof \WP_Term && $term->taxonomy === $taxonomy ) {
				$this->save_metadata_for_term( $term, true );
			}
		}, 10, 3 );

		add_action( 'created_term', function ( $term_id, $tt_id, $taxonomy ) {

			$term = get_term_by( 'term_taxonomy_id', $tt_id );
			if ( $term instanceof \WP_Term && $term->taxonomy === $taxonomy ) {
				$this->save_metadata_for_term( $term, false );
			}
		}, 10, 3 );
	}

	/**
	 * Adds all meta boxes returned by the meta box factory.
	 *
	 * @param \WP_Term $term Term object.
	 * @param bool     $update
	 *
	 * @return void
	 */
	private function add_meta_boxes( $term, bool $update ) {

		$controllers = $this->get_controllers( $term );
		if ( ! $controllers ) {
			return;
		}

		/**
		 * Fires right before the term meta boxes are added or saved.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term $term Term object.
		 * @param bool     $update
		 */
		do_action( self::ACTION_INIT_META_BOXES, $term, $update );

		if ( $this->ui instanceof MetaBoxUI ) {
			$this->ui->register_view();
		}

		/**
		 * Fires right before the term meta boxes are added.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term $term Term object.
		 * @param bool     $update
		 */
		do_action( self::ACTION_ADD_META_BOXES, $term, $update );

		array_walk( $controllers, function ( MetaBoxController $controller ) use ( $term, $update ) {

			if ( $this->is_meta_box_allowed_for_term( $controller, $term ) ) {
				$this->add_meta_box( $controller, $term, $update );

				/**
				 * Fires right after a term meta box was added.
				 *
				 * @since 3.0.0
				 *
				 * @param \WP_Term          $term       Term object.
				 * @param MetaBoxController $controller Meta box controller object.
				 */
				do_action( self::ACTION_ADDED_META_BOX, $term, $controller );
			}
		} );
	}

	/**
	 * Adds a meta box according to the data of the given controller.
	 *
	 * @param MetaBoxController $controller Meta box controller object.
	 * @param \WP_Term          $term
	 * @param bool              $update
	 *
	 * @return void
	 */
	private function add_meta_box( MetaBoxController $controller, \WP_Term $term, bool $update ) {

		$meta_box = $controller->meta_box();
		if ( ! $meta_box->is_allowed_for_screen( get_current_screen() ) ) {
			return;
		}

		$view = $controller->view();
		if ( ! $view instanceof TermMetaBoxView ) {
			return;
		}

		echo nonce_field( $this->create_nonce_for_meta_box( $meta_box ) );

		$meta_box = $controller->meta_box();

		echo $view->with_term( $term )->with_data( compact( 'meta_box', 'update' ) )->render();
	}

	/**
	 * Saves the metadata of all meta boxes for the given term.
	 *
	 * @param \WP_Term $source_term Term object.
	 * @param bool     $update      Whether or not this is an update of the term.
	 */
	private function save_metadata_for_term( \WP_Term $source_term, bool $update ) {

		$controllers = $this->get_controllers( $source_term );
		if ( ! $controllers ) {
			return;
		}

		$save_context = $this->factory->create_term_request_context( $source_term, $this->server_request );

		if ( ! $save_context[ SourceTermSaveContext::TERM_ID ] ) {
			return;
		}

		/**
		 * Fires right before the term meta boxes are added or saved.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term $source_term Term object.
		 */
		do_action( self::ACTION_INIT_META_BOXES, $source_term );

		if ( $this->ui instanceof MetaBoxUI ) {
			$this->ui->register_updater();
		}

		/**
		 * Fires right before the metadata of the meta boxes is saved.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term              $source_term  Source term object.
		 * @param SourceTermSaveContext $save_context Source term save context object.
		 * @param bool                  $update       Whether or not this is an update of the term.
		 */
		do_action( self::ACTION_SAVE_META_BOXES, $source_term, $save_context, $update );

		$network_state = NetworkState::from_globals();

		array_walk( $controllers,
			function ( MetaBoxController $controller ) use ( $source_term, $save_context, $update ) {

				if ( $this->is_meta_box_allowed_for_term( $controller, $source_term ) ) {

					/** @var SiteAwareMetaBoxController $controller */
					switch_to_blog( $controller->site_id() );

					$this->save_meta_box_data_for_term( $controller, $save_context, $update );

					/**
					 * Fires right after the metadata of a meta box was saved.
					 *
					 * Important: it runs in the site context of the remote term.
					 *
					 * @since 3.0.0
					 *
					 * @param SourceTermSaveContext $save_context Source term save context object.
					 * @param MetaBoxController     $controller   Meta box controller object.
					 */
					do_action( self::ACTION_SAVED_META_BOX_DATA, $save_context, $controller );
				}
			} );

		$network_state->restore();

		/**
		 * Fires right after the metadata of the meta boxes is saved.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term              $source_term  Term object.
		 * @param SourceTermSaveContext $save_context Source term save context object.
		 */
		do_action( self::ACTION_SAVED_META_BOXES, $source_term, $save_context );
	}

	/**
	 * Saves the metadata according to the given meta box controller for the given term.
	 *
	 * @param MetaBoxController     $controller   Meta box controller object.
	 * @param SourceTermSaveContext $save_context Save context object.
	 * @param bool                  $update       Whether or not this is an update of the term.
	 */
	private function save_meta_box_data_for_term(
		MetaBoxController $controller,
		SourceTermSaveContext $save_context,
		bool $update
	) {

		$updater = $controller->updater();
		if ( ! $updater instanceof TermMetaUpdater ) {
			return;
		}

		if ( ! $this->create_nonce_for_meta_box( $controller->meta_box() )->is_valid() ) {
			return;
		}

		$updater
			->with_term_save_context( $save_context )
			->with_data( compact( 'update' ) )
			->update( $this->server_request );
	}

	/**
	 * Returns a nonce specific to the given meta box.
	 *
	 * @param MetaBox $meta_box Meta box object.
	 *
	 * @return Nonce Nonce object.
	 */
	private function create_nonce_for_meta_box( MetaBox $meta_box ) {

		return $this->nonce_factory->create( [ 'meta_box_' . $meta_box->id() ] );
	}

	/**
	 * Returns the controllers for all meta boxes for the given term.
	 *
	 * @param \WP_Term|mixed $term Term object, maybe.
	 *
	 * @return SiteAwareMetaBoxController[] Meta box controllers.
	 */
	private function get_controllers( $term ): array {

		if ( ! $term instanceof \WP_Term ) {
			return [];
		}

		$allowed = false;

		if ( $term->term_id ) {
			$allowed = current_user_can( 'edit_term', $term->term_id );
		} elseif ( $term->taxonomy && ( $taxonomy_object = get_taxonomy( $term->taxonomy ) ) ) {
			$allowed = current_user_can( $taxonomy_object->cap->edit_terms );
		}

		return $allowed ? $this->factory->create_meta_boxes( $term ) : [];
	}

	/**
	 * Checks if the meta box for the given controller is to be displayed for the given term.
	 *
	 * @param MetaBoxController $controller Meta box controller object.
	 * @param \WP_Term          $term       Term object.
	 *
	 * @return bool Whether or not the meta box for the given controller is to be displayed for the given term.
	 */
	private function is_meta_box_allowed_for_term( MetaBoxController $controller, \WP_Term $term ): bool {

		return
			! $controller instanceof SiteAwareMetaBoxController
			|| $this->permission_checker->is_related_term_editable( $term, $controller->site_id() );
	}
}
