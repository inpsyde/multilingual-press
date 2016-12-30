<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Translation\Translator;

use Inpsyde\MultilingualPress\Factory\TypeFactory;
use Inpsyde\MultilingualPress\Translation\Translator;

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
	public function get_translation( $site_id, array $args = [] ) {

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
	private function get_translation_data( $post_id, $strict ) {

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
			do_action( 'mlp_before_link' );
			$url = get_permalink( $post_id );
			do_action( 'mlp_after_link' );

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
