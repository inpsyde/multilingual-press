<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI
 * @since   3.0.0
 */
class AdvancedPostTranslatorAJAXHandler {

	/**
	 * Ajax action used to update the metabox.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const AJAX_ACTION = 'mlp_process_post_data';

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ServerRequest $request
	 */
	public function __construct( ServerRequest $request ) {

		$this->server_request = $request;
	}

	/**
	 * Handle data processing via AJAX
	 */
	public function __invoke() {

		$current_site_id = get_current_blog_id();

		$current_post_id = (int) $this->server_request->body_value(
			'current_post_id',
			INPUT_POST,
			FILTER_SANITIZE_NUMBER_INT
		);

		$remote_site_id = (int) $this->server_request->body_value(
			'remote_site_id',
			INPUT_POST,
			FILTER_SANITIZE_NUMBER_INT
		);

		if ( ! $current_post_id || ! $remote_site_id ) {
			wp_send_json_error();
		}

		/**
		 * Filters a post's title for a remote site.
		 *
		 * @param string $title           Post title.
		 * @param int    $current_site_id Source site ID.
		 * @param int    $current_post_id Source post ID.
		 * @param int    $remote_site_id  Remote site ID.
		 */
		$title = apply_filters(
			'mlp_process_post_title_for_remote_site',
			$this->server_request->body_value( 'title', INPUT_POST ),
			$current_site_id,
			$current_post_id,
			$remote_site_id
		);

		/**
		 * Filters a post's slug for a remote site.
		 *
		 * @param string $slug            Post slug.
		 * @param int    $current_site_id Source site ID.
		 * @param int    $current_post_id Source post ID.
		 * @param int    $remote_site_id  Remote site ID.
		 */
		$slug = apply_filters(
			'mlp_process_post_slug_for_remote_site',
			$this->server_request->body_value( 'slug', INPUT_POST ),
			$current_site_id,
			$current_post_id,
			$remote_site_id
		);

		/**
		 * Filters a post's TinyMCE content for a remote site.
		 *
		 * @param string $content         Post content.
		 * @param int    $current_site_id Source site ID.
		 * @param int    $current_post_id Source post ID.
		 * @param int    $remote_site_id  Remote site ID.
		 */
		$tmce_content = (string) apply_filters(
			'mlp_process_post_tmce_content_for_remote_site',
			$this->server_request->body_value( 'tinyMceContent', INPUT_POST ),
			$current_site_id,
			$current_post_id,
			$remote_site_id
		);

		/**
		 * Filters a post's content for a remote site.
		 *
		 * @param string $content         Post content.
		 * @param int    $current_site_id Source site ID.
		 * @param int    $current_post_id Source post ID.
		 * @param int    $remote_site_id  Remote site ID.
		 */
		$content = (string) apply_filters(
			'mlp_process_post_content_for_remote_site',
			$this->server_request->body_value( 'content', INPUT_POST ),
			$current_site_id,
			$current_post_id,
			$remote_site_id
		);

		/**
		 * Filters a post's excerpt for a remote site.
		 *
		 * @param string $excerpt         Post excerpt.
		 * @param int    $current_site_id Source site ID.
		 * @param int    $current_post_id Source post ID.
		 * @param int    $remote_site_id  Remote site ID.
		 */
		$excerpt = apply_filters(
			'mlp_process_post_excerpt_for_remote_site',
			$this->server_request->body_value( 'excerpt', INPUT_POST ),
			$current_site_id,
			$current_post_id,
			$remote_site_id
		);

		$data = [
			'siteId'         => $remote_site_id,
			'title'          => esc_attr( $title ),
			'slug'           => esc_attr( $slug ),
			'tinyMceContent' => $tmce_content,
			'content'        => $content,
			'excerpt'        => $excerpt,
		];

		/**
		 * Filters a post's data for a remote site.
		 *
		 * @param array $data            Post data.
		 * @param int   $current_site_id Source site ID.
		 * @param int   $current_post_id Source post ID.
		 * @param int   $remote_site_id  Remote site ID.
		 */
		$filtered_data = (array) apply_filters(
			'mlp_process_post_data_for_remote_site',
			$data,
			$current_site_id,
			$current_post_id,
			$remote_site_id
		);

		wp_send_json_success( array_merge( $data, $filtered_data ) );
	}
}
