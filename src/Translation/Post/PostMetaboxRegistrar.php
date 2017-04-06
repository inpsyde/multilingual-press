<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Translation\Metabox\Metabox;
use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxInfo;
use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxRegistrar;
use Inpsyde\MultilingualPress\Translation\Metabox\PostMetaboxUpdater;
use Inpsyde\MultilingualPress\Translation\Metabox\PriorityAwareMetaboxInfo;
use Inpsyde\MultilingualPress\Translation\Metabox\SiteSpecificMetabox;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Implementation of MetaboxRegistrar for post metaboxes.
 *
 * Uses an injected factory to generate all the metabox objects that are added on "add_meta_boxes" and saved on
 * "save_post".
 *
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class PostMetaboxRegistrar implements MetaboxRegistrar {

	const ACTION_ADD_BOXES = 'multilingualpress.add_post_meta_boxes';

	const ACTION_SAVE_BOXES = 'multilingualpress.add_post_meta_boxes';

	const ACTION_BOX_ADDED = 'multilingualpress.post_meta_box_added';

	const ACTION_BOX_SAVED = 'multilingualpress.post_meta_box_saved';

	/**
	 * @var NonceFactory
	 */
	private $nonce_factory;

	/**
	 * @var ServerRequest
	 */
	private $request;

	/**
	 * @var PermissionChecker
	 */
	private $permission_checker;

	/**
	 * @var MetaboxFactory
	 */
	private $metabox_factory;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param MetaboxFactory    $metabox_factory
	 * @param PermissionChecker $permission_checker
	 * @param ServerRequest     $request
	 * @param NonceFactory      $nonce_factory
	 */
	public function __construct(
		MetaboxFactory $metabox_factory,
		PermissionChecker $permission_checker,
		ServerRequest $request,
		NonceFactory $nonce_factory
	) {

		$this->metabox_factory = $metabox_factory;

		$this->permission_checker = $permission_checker;

		$this->nonce_factory = $nonce_factory;

		$this->request = $request;
	}

	/**
	 * @return void
	 */
	public function register_metaboxes() {

		// Add metaboxes
		add_action( 'add_meta_boxes', function ( $post_type, $post ) {

			$this->add_boxes( $post, (string) $post_type );

		}, 10, 2 );

		// Save metaboxes
		add_action( 'save_post', function ( $post_id, $post, $update ) {

			$this->save_boxes( $post, (bool) $update );
		}, 10, 2 );
	}

	/**
	 * Add all metaboxes that are returned by the metabox factory, by calling in loop `add_metabox()` method that
	 * proxies a call to WordPress `add_meta_box()` function.
	 *
	 * @param \WP_Post $post
	 *
	 * @param string   $post_type
	 *
	 * @see MetaboxRegistrar::add_metabox()
	 */
	private function add_boxes( $post, string $post_type ) {

		$metaboxes = $this->factory_boxes( $post );

		if ( ! $metaboxes ) {
			return;
		}

		/**
		 * Runs before registration of the post meta boxes.
		 *
		 * @param \WP_Post $post Post object.
		 */
		do_action( self::ACTION_ADD_BOXES, $post );

		array_walk( $metaboxes, function ( Metabox $metabox ) use ( $post_type, $post ) {

			if ( $this->is_box_allowed_for_post( $metabox, $post ) ) {

				$this->add_metabox( $metabox, $post_type );

				/**
				 * Runs after registration of each post meta box
				 *
				 * @param \WP_Post $post    Post object.
				 * @param Metabox  $metabox Metabox object
				 */
				do_action( self::ACTION_BOX_ADDED, $post, $metabox );
			}

		} );

	}

	/**
	 * Adds a metabox via WordPress `add_meta_box()` function, taking arguments mostly from info object and using
	 * view object for render callback.
	 *
	 * @param Metabox $metabox
	 * @param string  $post_type
	 */
	private function add_metabox( Metabox $metabox, string $post_type ) {

		$info = $metabox->info();
		if ( ! $info->is_allowed_for_screen( get_current_screen() ) ) {
			return;
		}

		$view = $metabox->view();
		if ( ! $view instanceof PostMetaboxView ) {
			return;
		}

		add_meta_box(
			$info->id(),
			esc_html( $info->title() ),
			function ( \WP_Post $post ) use ( $info, $view ) {

				echo nonce_field( $this->create_nonce( $info ) );

				echo $view->with_post( $post )->render();
			},
			$post_type,
			$info->context(),
			$info instanceof PriorityAwareMetaboxInfo ? $info->priority() : 'default'
		);
	}

	/**
	 * @param \WP_Post $post
	 * @param bool     $update
	 */
	private function save_boxes( \WP_Post $post, bool $update ) {

		$metaboxes = $this->factory_boxes( $post );

		if ( ! $metaboxes ) {
			return;
		}

		/**
		 * Runs before saving of the post meta boxes.
		 *
		 * @param \WP_Post $post   Post object.
		 * @param bool     $update True if it is a post update
		 */
		do_action( self::ACTION_SAVE_BOXES, $post, $update );

		array_walk( $metaboxes, function ( Metabox $metabox ) use ( $post, $update ) {

			if ( $this->is_box_allowed_for_post( $metabox, $post ) ) {

				$this->save_metabox( $metabox, $post, $update );

				/**
				 * Runs after saving of each post meta box
				 *
				 * @param \WP_Post $post    Post object.
				 * @param Metabox  $metabox Metabox object
				 */
				do_action( self::ACTION_BOX_SAVED, $post, $metabox );
			}
		} );
	}

	/**
	 * @param Metabox  $metabox
	 * @param \WP_Post $post
	 * @param bool     $update
	 */
	private function save_metabox( Metabox $metabox, \WP_Post $post, bool $update ) {

		$updater = $metabox->updater();
		if ( ! $updater instanceof PostMetaboxUpdater ) {
			return;
		}

		if ( ! $this->create_nonce( $metabox->info() )->is_valid() ) {
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
	 * @return array
	 */
	private function factory_boxes( $post ): array {

		if ( $post instanceof \WP_Post || ! $this->permission_checker->is_source_post_editable( $post ) ) {
			return [];
		}

		return $this->metabox_factory->create_boxes( $post );
	}

	/**
	 * @param Metabox  $metabox
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	private function is_box_allowed_for_post( Metabox $metabox, \WP_Post $post ): bool {

		return
			! $metabox instanceof SiteSpecificMetabox
			|| $this->permission_checker->is_remote_post_editable( $post, $metabox->site_id() );
	}

	/**
	 * @param MetaboxInfo $info
	 *
	 * @return Nonce
	 */
	private function create_nonce( MetaboxInfo $info ) {

		return $this->nonce_factory->create( [ 'metabox_' . $info->id() ] );
	}

}