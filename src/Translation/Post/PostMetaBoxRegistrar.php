<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\UIAwareMetaBoxRegistrar;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\NetworkState;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxController;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post\PostMetaBoxView;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post\PostMetaUpdater;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\PriorityAwareMetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\SourcePostSaveContext;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Meta box registrar implementation for post meta boxes.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post
 * @since   3.0.0
 */
final class PostMetaBoxRegistrar implements UIAwareMetaBoxRegistrar {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_INIT_META_BOXES = 'multilingualpress.init_post_meta_boxes';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_ADD_META_BOXES = 'multilingualpress.add_post_meta_boxes';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_ADDED_META_BOX = 'multilingualpress.added_post_meta_box';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_SAVE_META_BOXES = 'multilingualpress.save_post_meta_boxes';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_SAVED_META_BOXES = 'multilingualpress.saved_post_meta_boxes';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_SAVED_META_BOX_DATA = 'multilingualpress.saved_post_meta_box_data';

	/**
	 * @var int
	 */
	private $current_site_id;

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

		$this->current_site_id = get_current_blog_id();
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
	 *
	 * @throws \BadMethodCallException If there already has been set a user interface.
	 */
	public function set_ui( MetaBoxUI $ui ): UIAwareMetaBoxRegistrar {

		// Don't allow overwrite.
		if ( $this->ui ) {
			throw new \BadMethodCallException( sprintf( 'It is not possible to override UI for %s.', __CLASS__ ) );
		}

		// Don't do anything if called too early.
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

		add_action( 'add_meta_boxes', function ( $post_type, $post ) {

			$this->add_meta_boxes( $post, (string) $post_type );
		}, 10, 2 );

		/** @noinspection PhpUnusedParameterInspection */
		add_action( 'save_post', function ( $post_id, \WP_Post $post, $update ) {

			$this->save_metadata_for_post( $post, (bool) $update );
		}, 10, 3 );
	}

	/**
	 * Adds all meta boxes returned by the meta box factory.
	 *
	 * @param \WP_Post $post      Post object.
	 * @param string   $post_type Post type slug.
	 *
	 * @return void
	 */
	private function add_meta_boxes( $post, string $post_type ) {

		$controllers = $this->get_controllers( $post );
		if ( ! $controllers ) {
			return;
		}

		$this->initialize_meta_boxes( $post );

		if ( $this->ui ) {
			$this->ui->register_view();
		}

		/**
		 * Fires right before the post meta boxes are added.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Post $post Post object.
		 */
		do_action( self::ACTION_ADD_META_BOXES, $post );

		array_walk( $controllers, function ( MetaBoxController $controller ) use ( $post_type, $post ) {

			if ( $this->is_meta_box_allowed_for_post( $controller, $post ) ) {
				$this->add_meta_box( $controller, $post_type );

				/**
				 * Fires right after a post meta box was added.
				 *
				 * @since 3.0.0
				 *
				 * @param \WP_Post          $post       Post object.
				 * @param MetaBoxController $controller Meta box controller object.
				 */
				do_action( self::ACTION_ADDED_META_BOX, $post, $controller );
			}
		} );
	}

	/**
	 * Adds a meta box according to the data of the given controller.
	 *
	 * @param MetaBoxController $controller Meta box controller object.
	 * @param string            $post_type  Post type slug.
	 *
	 * @return void
	 */
	private function add_meta_box( MetaBoxController $controller, string $post_type ) {

		$meta_box = $controller->meta_box();
		if ( ! $meta_box->is_allowed_for_screen( get_current_screen() ) ) {
			return;
		}

		$view = $controller->view();
		if ( ! $view instanceof PostMetaBoxView ) {
			return;
		}

		$priority = $meta_box instanceof PriorityAwareMetaBox ? $meta_box->priority() : 'default';

		add_meta_box(
			$meta_box->id(),
			$meta_box->title(),
			function ( \WP_Post $post ) use ( $meta_box, $view ) {

				nonce_field( $this->create_nonce_for_meta_box( $meta_box ) );
				echo $view->with_post( $post )->render();
			},
			$post_type,
			$meta_box->context(),
			$priority
		);
	}

	/**
	 * Saves the metadata of all meta boxes for the given post.
	 *
	 * @param \WP_Post $post   Post object.
	 * @param bool     $update Whether or not this is an update of the post.
	 */
	private function save_metadata_for_post( \WP_Post $post, bool $update ) {

		$controllers = $this->get_controllers( $post );
		if ( ! $controllers ) {
			return;
		}

		$save_context = $this->factory->create_post_request_context( $post, $this->server_request );
		if ( ! $save_context[ SourcePostSaveContext::POST_ID ] ) {
			return;
		}

		$this->initialize_meta_boxes( $post );

		if ( $this->ui ) {
			$this->ui->register_updater();
		}

		/**
		 * Fires right before the metadata of the meta boxes is saved.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Post              $post         Source post object.
		 * @param SourcePostSaveContext $save_context Source post save context object.
		 * @param bool                  $update       Whether or not this is an update of the post.
		 */
		do_action( self::ACTION_SAVE_META_BOXES, $post, $save_context, $update );

		$network_state = NetworkState::create();

		array_walk( $controllers, function ( MetaBoxController $controller ) use ( $post, $save_context, $update ) {

			if ( $this->is_meta_box_allowed_for_post( $controller, $post ) ) {
				/** @var SiteAwareMetaBoxController $controller */
				switch_to_blog( $controller->site_id() );

				$this->save_meta_box_data_for_post( $controller, $save_context, $update );

				/**
				 * Fires right after the metadata of a meta box was saved.
				 *
				 * Important: it runs in the site context of the remote post.
				 *
				 * @since 3.0.0
				 *
				 * @param SourcePostSaveContext $save_context Source post save context object.
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
		 * @param \WP_Post              $post         Post object.
		 * @param SourcePostSaveContext $save_context Source post save context object.
		 */
		do_action( self::ACTION_SAVED_META_BOXES, $post, $save_context );
	}

	/**
	 * Saves the metadata according to the given meta box controller for the given post.
	 *
	 * @param MetaBoxController     $controller   Meta box controller object.
	 * @param SourcePostSaveContext $save_context Save context object.
	 * @param bool                  $update       Whether or not this is an update of the post.
	 */
	private function save_meta_box_data_for_post(
		MetaBoxController $controller,
		SourcePostSaveContext $save_context,
		bool $update
	) {

		$updater = $controller->updater();
		if ( ! $updater instanceof PostMetaUpdater ) {
			return;
		}

		if ( ! $this->create_nonce_for_meta_box( $controller->meta_box() )->is_valid() ) {
			return;
		}

		$updater
			->with_post_save_context( $save_context )
			->with_data( compact( 'update' ) )
			->update( $this->server_request );
	}

	/**
	 * Triggers the initialization of the meta boxes for the given post.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return void
	 */
	private function initialize_meta_boxes( \WP_Post $post ) {

		static $initialized;
		if ( $initialized ) {
			return;
		}

		/**
		 * Fires when the meta boxes are about to be initialized for either display or data processing.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Post $post Post object.
		 */
		do_action( self::ACTION_INIT_META_BOXES, $post );

		$initialized = true;
	}

	/**
	 * Returns a nonce specific to the given meta box.
	 *
	 * @param MetaBox $meta_box Meta box object.
	 *
	 * @return Nonce Nonce object.
	 */
	private function create_nonce_for_meta_box( MetaBox $meta_box ) {

		/** @var WPNonce $nonce */
		$nonce = $this->nonce_factory->create( [ 'meta_box_' . $meta_box->id() ], WPNonce::class );

		return $nonce->with_site( $this->current_site_id );
	}

	/**
	 * Returns the controllers for all meta boxes for the given post.
	 *
	 * @param mixed $post Post object, maybe.
	 *
	 * @return SiteAwareMetaBoxController[] Meta box controllers.
	 */
	private function get_controllers( $post ): array {

		if ( ! $post instanceof \WP_Post || ! $this->is_post_editable( $post ) ) {
			return [];
		}

		return $this->factory->create_meta_boxes( $post );
	}

	/**
	 * Checks if the meta box for the given controller is to be displayed for the given post.
	 *
	 * @param MetaBoxController $controller Meta box controller object.
	 * @param \WP_Post          $post       Post object.
	 *
	 * @return bool Whether or not the meta box for the given controller is to be displayed for the given post.
	 */
	private function is_meta_box_allowed_for_post( MetaBoxController $controller, \WP_Post $post ): bool {

		return
			! $controller instanceof SiteAwareMetaBoxController
			|| $this->permission_checker->is_related_post_editable( $post, $controller->site_id() );
	}

	/**
	 * Checks if the current user can edit the given post in the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return bool Whether or not the current user can edit the given post in the current site.
	 */
	private function is_post_editable( \WP_Post $post ): bool {

		$post_type = get_post_type_object( $post->post_type );
		if ( ! $post_type instanceof \WP_Post_Type ) {
			return false;
		}

		return current_user_can( $post_type->cap->edit_post, $post->ID );
	}
}
