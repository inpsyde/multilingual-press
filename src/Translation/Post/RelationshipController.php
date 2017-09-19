<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Common\HTTP\Request;

/**
 * Relationship controller.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post
 * @since   3.0.0
 */
class RelationshipController {

	/**
	 * Action to be used in requests.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_CONNECT_EXISTING = 'mlp_rc_connect_existing_post';

	/**
	 * Action to be used in requests.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_CONNECT_NEW = 'mlp_rc_connect_new_post';

	/**
	 * Action to be used in requests.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_DISCONNECT = 'mlp_rc_disconnect_post';

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var RelationshipContext
	 */
	private $context;

	/**
	 * @var \WP_Error
	 */
	private $last_error = null;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ContentRelations $content_relations Content relations API object.
	 * @param Request          $request           HTTP request object.
	 */
	public function __construct( ContentRelations $content_relations, Request $request ) {

		$this->content_relations = $content_relations;

		$this->request = $request;

		$this->context = RelationshipContext::from_request( $request );
	}

	/**
	 * Initializes the relationship controller.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function initialize() {

		$action = (string) $this->request->body_value( 'action', INPUT_REQUEST, FILTER_SANITIZE_STRING );
		if ( '' === $action ) {
			return;
		}

		$callback = $this->get_callback( $action );
		if ( $callback ) {
			add_action( "wp_ajax_{$action}", $callback );
		}
	}

	/**
	 * Connects the current post with an existing remote one.
	 *
	 * @since   3.0.0
	 * @wp-hook wp_ajax_{$action}
	 *
	 * @return void
	 */
	public function handle_connect_existing_post() {

		if ( $this->connect_existing_post() ) {
			wp_send_json_success();
		}

		wp_send_json_error( $this->last_error );
	}

	/**
	 * Connects the current post with a new remote one.
	 *
	 * @since   3.0.0
	 * @wp-hook wp_ajax_{$action}
	 *
	 * @return void
	 */
	public function handle_connect_new_post() {

		if ( $this->connect_new_post() ) {
			wp_send_json_success();
		}

		wp_send_json_error( $this->last_error );
	}

	/**
	 * Deletes the relation of the post with the given ID.
	 *
	 * @since   3.0.0
	 * @wp-hook deleted_post
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool Whether or not the post was handled successfully.
	 */
	public function handle_deleted_post( $post_id ): bool {

		return $this->delete_relation( (int) get_current_blog_id(), (int) $post_id );
	}

	/**
	 * Deletes the relation for the given arguments.
	 *
	 * @param int $site_id Site ID.
	 * @param int $post_id Post ID.
	 *
	 * @return bool Whether or not the post was handled successfully.
	 */
	private function delete_relation( int $site_id, int $post_id ): bool {

		return $this->content_relations->delete_relation( [
			$site_id => $post_id,
		], ContentRelations::CONTENT_TYPE_POST );
	}

	/**
	 * Disconnects the current post and the one given in the request.
	 *
	 * @since   3.0.0
	 * @wp-hook wp_ajax_{$action}
	 *
	 * @return void
	 */
	public function handle_disconnect_post() {

		if ( $this->disconnect_post() ) {
			wp_send_json_success();
		}

		wp_send_json_error( $this->last_error );
	}

	/**
	 * Connects the current post with a new remote one.
	 *
	 * @return bool Whether or not the relationship was updated successfully, or an error object.
	 */
	private function connect_new_post(): bool {

		$source_post = $this->context->source_post();
		if ( ! $source_post ) {
			return false;
		}

		$remote_site_id = $this->context->remote_site_id();

		$post_id = (int) $this->request->body_value( 'post_ID', INPUT_POST, FILTER_SANITIZE_NUMBER_INT );

		$save_context = [
			'source_blog'    => $this->context->source_site_id(),
			'source_post'    => $source_post,
			'real_post_type' => $this->get_real_post_type( $source_post ),
			'real_post_id'   => $post_id ?: $this->context->source_post_id(),
		];

		/** This action is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
		do_action( 'mlp_before_post_synchronization', $save_context );

		switch_to_blog( $remote_site_id );

		$new_post_id = wp_insert_post( [
			'post_type'   => $source_post->post_type,
			'post_status' => 'draft',
			'post_title'  => $this->context->new_post_title(),
		], true );

		restore_current_blog();

		$save_context['target_blog_id'] = $remote_site_id;

		/** This action is documented in inc/advanced-translator/Mlp_Advanced_Translator_Data.php */
		do_action( 'mlp_after_post_synchronization', $save_context );

		if ( is_wp_error( $new_post_id ) ) {
			$this->last_error = $new_post_id;

			return false;
		}

		$this->context = RelationshipContext::from_existing( $this->context, [
			RelationshipContext::KEY_REMOTE_POST_ID => $new_post_id,
		] );

		return $this->connect_existing_post();
	}

	/**
	 * Connects the current post with an existing remote one.
	 *
	 * @return bool Whether or not the relationship was updated successfully.
	 */
	private function connect_existing_post(): bool {

		$content_ids = [
			$this->context->source_site_id() => $this->context->source_post_id(),
			$this->context->remote_site_id() => $this->context->remote_post_id(),
		];

		$relationship_id = $this->content_relations->get_relationship_id(
			$content_ids,
			ContentRelations::CONTENT_TYPE_POST,
			true
		);
		if ( ! $relationship_id ) {
			return false;
		}

		foreach ( $content_ids as $site_id => $post_id ) {
			if ( ! $this->content_relations->set_relation( $relationship_id, $site_id, $post_id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Disconnects the current post with the one given in the request.
	 *
	 * @return bool Whether or not the post was disconnected successfully.
	 */
	private function disconnect_post() {

		return $this->delete_relation( $this->context->remote_site_id(), $this->context->remote_post_id() );
	}

	/**
	 * Returns the appropriate callback for the given action.
	 *
	 * @param string $action Action.
	 *
	 * @return callable Callback, of null on failure.
	 */
	private function get_callback( string $action ) {

		switch ( $action ) {
			case static::ACTION_CONNECT_EXISTING:
				return [ $this, 'handle_connect_existing_post' ];

			case static::ACTION_CONNECT_NEW:
				return [ $this, 'handle_connect_new_post' ];

			case static::ACTION_DISCONNECT:
				return [ $this, 'handle_disconnect_post' ];
		}

		return null;
	}

	/**
	 * Returns the post type of the "real" post according to the given one.
	 *
	 * This includes a workaround for auto-drafts.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return string Post type.
	 */
	private function get_real_post_type( \WP_Post $post ): string {

		if ( 'revision' !== $post->post_type ) {
			return $post->post_type;
		}

		$post_type = $this->request->body_value( 'post_type', INPUT_POST, FILTER_SANITIZE_STRING );
		if ( is_string( $post_type ) && '' !== $post_type && 'revision' !== $post_type ) {
			return $post_type;
		}

		return $post->post_type;
	}
}
