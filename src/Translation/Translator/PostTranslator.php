<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Translator;

use Inpsyde\MultilingualPress\Factory\TypeFactory;

/**
 * Translator implementation for posts.
 *
 * @package Inpsyde\MultilingualPress\Translation
 * @since   3.0.0
 */
final class PostTranslator implements Translator {

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TypeFactory $type_factory Type factory object.
	 */
	public function __construct( TypeFactory $type_factory ) {

		$this->type_factory = $type_factory;
	}

	/**
	 * Returns the translation data for the given site, according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $site_id Site ID.
	 * @param array $args    Optional. Arguments required to fetch translation. Defaults to empty array.
	 *
	 * @return array Translation data.
	 */
	public function get_translation( int $site_id, array $args = [] ): array {

		if ( empty( $args['content_id'] ) ) {
			return [];
		}

		switch_to_blog( $site_id );

		$data = $this->get_translation_data( (int) $args['content_id'], ! empty( $args['strict'] ) );

		restore_current_blog();

		return $data;
	}

	/**
	 * Returns the translation data for the given post ID.
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $strict  Only respect posts that have a translation?
	 *
	 * @return array Translation data.
	 */
	private function get_translation_data( int $post_id, bool $strict ): array {

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [];
		}

		if ( is_admin() ) {
			return current_user_can( 'edit_post', $post_id )
				? [
					'remote_title' => get_the_title( $post_id ),
					'remote_url'   => $this->type_factory->create_url( [
						get_edit_post_link( $post_id ),
					] ),
				]
				: [];
		}

		if ( 'publish' === $post->post_status || current_user_can( 'edit_post', $post_id ) ) {
			/**
			 * Fires right before MultilingualPress generates a permalink.
			 *
			 * @since 3.0.0
			 *
			 * @param int $object_id Object ID.
			 */
			do_action( 'multilingualpress.generate_permalink', $post_id );

			$url = get_permalink( $post_id );

			/**
			 * Fires right after MultilingualPress generated a permalink.
			 *
			 * @since 3.0.0
			 *
			 * @param int $object_id Object ID.
			 */
			do_action( 'multilingualpress.generated_permalink', $post_id );

			return [
				'remote_title' => get_the_title( $post_id ),
				'remote_url'   => $this->type_factory->create_url( [
					$url ?: '',
				] ),
			];
		}

		return $strict
			? []
			: [
				'remote_title' => get_the_title( $post_id ),
				'remote_url'   => '',
			];
	}
}
