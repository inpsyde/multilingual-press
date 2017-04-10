<?php # -*- coding: utf-8 -*-

// TODO

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxController;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxRegistrar;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post\PostMetaBoxView;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post\PostMetaUpdater;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\PriorityAwareMetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Implementation of MetaBoxRegistrar for post metaboxes.
 *
 * Uses an injected factory to generate all the metabox objects that are added on "add_meta_boxes" and saved on
 * "save_post".
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
final class PostMetaBoxRegistrar implements MetaBoxRegistrar {

	const ACTION_ADD_BOXES = 'multilingualpress.add_post_meta_boxes';

	const ACTION_BOX_ADDED = 'multilingualpress.post_meta_box_added';

	const ACTION_BOX_SAVED = 'multilingualpress.post_meta_box_saved';

	const ACTION_SAVE_BOXES = 'multilingualpress.save_post_meta_boxes';

	/**
	 * @var MetaBoxFactory
	 */
	private $factory;

	/**
	 * @var NonceFactory
	 */
	private $nonce_factory;

	/**
	 * @var PermissionChecker
	 */
	private $permission_checker;

	/**
	 * @var ServerRequest
	 */
	private $request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param MetaBoxFactory    $factory
	 * @param PermissionChecker $permission_checker
	 * @param ServerRequest     $request
	 * @param NonceFactory      $nonce_factory
	 */
	public function __construct(
		MetaBoxFactory $factory,
		PermissionChecker $permission_checker,
		ServerRequest $request,
		NonceFactory $nonce_factory
	) {

		$this->factory = $factory;

		$this->permission_checker = $permission_checker;

		$this->nonce_factory = $nonce_factory;

		$this->request = $request;
	}

	/**
	 * @return void
	 */
	public function register_meta_boxes() {

		add_action( 'add_meta_boxes', function ( $post_type, $post ) {

			$this->add_meta_boxes( $post, (string) $post_type );
		}, 10, 2 );

		add_action( 'save_post', function ( $post_id, \WP_Post $post, $update ) {

			$this->save_metadata_for_post( $post, (bool) $update );
		}, 10, 3 );
	}

	/**
	 * Add all metaboxes that are returned by the metabox factory, by calling in loop `add_metabox()` method that
	 * proxies a call to WordPress `add_meta_box()` function.
	 *
	 * @param \WP_Post $post
	 * @param string   $post_type
	 *
	 * @see MetaBoxRegistrar::add_metabox()
	 */
	private function add_meta_boxes( $post, string $post_type ) {

		$controllers = $this->get_controllers( $post );
		if ( ! $controllers ) {
			return;
		}

		/**
		 * Runs before registration of the post meta boxes.
		 *
		 * @param \WP_Post $post Post object.
		 */
		do_action( self::ACTION_ADD_BOXES, $post );

		array_walk( $controllers, function ( MetaBoxController $controller ) use ( $post_type, $post ) {

			if ( $this->is_meta_box_allowed_for_post( $controller, $post ) ) {
				$this->add_meta_box( $controller, $post_type );

				/**
				 * Runs after registration of each post meta box
				 *
				 * @param \WP_Post $post    Post object.
				 * @param MetaBoxController  $controller Meta box controller object.
				 */
				do_action( self::ACTION_BOX_ADDED, $post, $controller );
			}
		} );
	}

	/**
	 * Adds a metabox via WordPress `add_meta_box()` function, taking arguments mostly from info object and using
	 * view object for render callback.
	 *
	 * @param MetaBoxController $controller
	 * @param string  $post_type
	 */
	private function add_meta_box( MetaBoxController $controller, string $post_type ) {

		$info = $controller->meta_box();
		if ( ! $info->is_allowed_for_screen( get_current_screen() ) ) {
			return;
		}

		$view = $controller->view();
		if ( ! $view instanceof PostMetaBoxView ) {
			return;
		}

		add_meta_box(
			$info->id(),
			esc_html( $info->title() ),
			function ( \WP_Post $post ) use ( $info, $view ) {

				echo nonce_field( $this->create_nonce_for_meta_box( $info ) );
				echo $view->with_post( $post )->render();
			},
			$post_type,
			$info->context(),
			$info instanceof PriorityAwareMetaBox ? $info->priority() : 'default'
		);
	}

	/**
	 * @param \WP_Post $post
	 * @param bool     $update
	 */
	private function save_metadata_for_post( \WP_Post $post, bool $update ) {

		$controllers = $this->get_controllers( $post );
		if ( ! $controllers ) {
			return;
		}

		/**
		 * Runs before saving of the post meta boxes.
		 *
		 * @param \WP_Post $post   Post object.
		 * @param bool     $update True if it is a post update
		 */
		do_action( self::ACTION_SAVE_BOXES, $post, $update );

		array_walk( $controllers, function ( MetaBoxController $controller ) use ( $post, $update ) {

			if ( $this->is_meta_box_allowed_for_post( $controller, $post ) ) {
				$this->save_meta_box_data_for_post( $controller, $post, $update );

				/**
				 * Runs after saving of each post meta box
				 *
				 * @param \WP_Post $post    Post object.
				 * @param MetaBoxController  $controller MetaBox object
				 */
				do_action( self::ACTION_BOX_SAVED, $post, $controller );
			}
		} );
	}

	/**
	 * @param MetaBoxController  $controller
	 * @param \WP_Post $post
	 * @param bool     $update
	 */
	private function save_meta_box_data_for_post( MetaBoxController $controller, \WP_Post $post, bool $update ) {

		$updater = $controller->updater();
		if ( ! $updater instanceof PostMetaUpdater ) {
			return;
		}

		if ( ! $this->create_nonce_for_meta_box( $controller->meta_box() )->is_valid() ) {
			return;
		}

		$updater
			->with_post( $post )
			->with_data( compact( 'update' ) )
			->update( $this->request );
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return SiteAwareMetaBoxController[]
	 */
	private function get_controllers( $post ): array {

		if ( $post instanceof \WP_Post || ! $this->permission_checker->is_post_editable( $post ) ) {
			return [];
		}

		return $this->factory->create_meta_boxes( $post );
	}

	/**
	 * @param MetaBoxController  $controller
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	private function is_meta_box_allowed_for_post( MetaBoxController $controller, \WP_Post $post ): bool {

		return
			! $controller instanceof SiteAwareMetaBoxController
			|| $this->permission_checker->is_translation_editable( $post, $controller->site_id() );
	}

	/**
	 * @param MetaBox $info
	 *
	 * @return Nonce
	 */
	private function create_nonce_for_meta_box( MetaBox $info ) {

		return $this->nonce_factory->create( [ 'meta_box_' . $info->id() ] );
	}
}
