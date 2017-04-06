<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Translation\Metabox\Metabox;
use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxInfo;
use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxRegistrar;
use Inpsyde\MultilingualPress\Translation\Metabox\PriorityAwareMetaboxInfo;
use Inpsyde\MultilingualPress\Translation\Metabox\SiteSpecificMetabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class PostMetaboxRegistrar implements MetaboxRegistrar {

	/**
	 * @var NonceFactory
	 */
	private $nonce_factory;

	/**
	 * @var ServerRequest
	 */
	private $request;
	/**
	 * @var PostTranslationGuard
	 */
	private $post_translation_guard;

	/**
	 * @var PostTranslationMetaboxFactory
	 */
	private $metabox_factory;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param PostTranslationMetaboxFactory $metabox_factory
	 * @param PostTranslationGuard          $post_translation_guard
	 * @param ServerRequest                 $request
	 * @param NonceFactory                  $nonce_factory
	 */
	public function __construct(
		PostTranslationMetaboxFactory $metabox_factory,
		PostTranslationGuard $post_translation_guard,
		ServerRequest $request,
		NonceFactory $nonce_factory
	) {

		$this->metabox_factory = $metabox_factory;

		$this->post_translation_guard = $post_translation_guard;

		$this->nonce_factory = $nonce_factory;

		$this->request = $request;
	}

	/**
	 * @return void
	 */
	public function register_metaboxes() {

		// Add metaboxes
		add_action( 'add_meta_boxes', function ( $post_type, $post ) {

			if ( $post instanceof \WP_Post || ! $this->post_translation_guard->is_source_post_translatable( $post ) ) {
				return;
			}

			$metaboxes = $this->metabox_factory->create_boxes( $post );

			/**
			 * Runs before registration of the translation meta boxes.
			 *
			 * @param \WP_Post $post Post object.
			 */
			do_action( 'multilingualpress.add_translation_meta_boxes', $post );

			array_walk( $metaboxes, function ( Metabox $metabox ) use ( $post_type, $post ) {

				if ( $this->is_post_allowed_for_box( $metabox, $post ) ) {

					$this->add_metabox( $metabox, $post_type );

					/**
					 * Runs after registration of each translation meta box
					 *
					 * @param \WP_Post $post    Post object.
					 * @param Metabox  $metabox Metabox object
					 */
					do_action( 'multilingualpress.translation_meta_box_registered', $post, $metabox );
				}

			} );
		} );

		// Save metaboxes
		add_action( 'save_post', function ( $post_id, $post, $update ) {

			if ( ! $this->post_translation_guard->is_source_post_translatable( $post ) ) {
				return;
			}

			$metaboxes = $this->metabox_factory->create_boxes( $post );

			/**
			 * Runs before saving of the translation meta boxes.
			 *
			 * @param \WP_Post $post   Post object.
			 * @param bool     $update True if it is a post update
			 */
			do_action( 'multilingualpress.save_translation_meta_boxes', $post, $update );

			array_walk( $metaboxes, function ( Metabox $metabox ) use ( $post, $update ) {

				if ( $this->is_post_allowed_for_box( $metabox, $post ) ) {

					$this->save_metabox( $metabox, $post, $update );

					/**
					 * Runs after saving of each translation meta box
					 *
					 * @param \WP_Post $post    Post object.
					 * @param Metabox  $metabox Metabox object
					 */
					do_action( 'multilingualpress.translation_meta_box_saved', $post, $metabox );
				}
			} );
		} );

	}

	/**
	 * @param Metabox $metabox
	 * @param string  $post_type
	 */
	private function add_metabox( Metabox $metabox, string $post_type ) {

		$info = $metabox->info();
		if ( $this->is_box_allowed( $info ) ) {
			return;
		}

		add_meta_box(
			$info->id(),
			esc_html( $info->title() ),
			function ( \WP_Post $post ) use ( $metabox, $info ) {

				$nonce = $this->create_nonce( $info );
				printf( '<input type="hidden" name="%1$s" value="%2$s" />', $nonce->action(), (string) $nonce );

				echo $metabox->view()->with_data( compact( 'post' ) )->render();
			},
			$post_type,
			$info->context(),
			$info instanceof PriorityAwareMetaboxInfo ? $info->priority() : 'default'
		);
	}

	/**
	 * @param Metabox  $metabox
	 * @param \WP_Post $post
	 * @param bool     $update
	 */
	private function save_metabox( Metabox $metabox, \WP_Post $post, bool $update ) {

		if ( $this->is_box_allowed( null, $this->create_nonce( $metabox->info() ) ) ) {
			return;
		}

		$metabox
			->updater()
			->with_data( compact( 'post', 'update' ) )
			->update( $this->request );
	}

	/**
	 * @param Metabox  $metabox
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	private function is_post_allowed_for_box( Metabox $metabox, \WP_Post $post ): bool {

		return
			! $metabox instanceof SiteSpecificMetabox
			|| $this->post_translation_guard->is_remote_post_translatable( $post, $metabox->site_id() );
	}

	/**
	 * @param MetaboxInfo|null $info
	 * @param Nonce|null       $nonce
	 *
	 * @return bool
	 */
	private function is_box_allowed( MetaboxInfo $info = null, Nonce $nonce = null ): bool {

		if ( $info && ! $info->is_allowed_for_screen( get_current_screen() ) ) {
			return false;
		}

		if ( $nonce && ! $nonce->is_valid() ) {
			return false;
		}

		return true;
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