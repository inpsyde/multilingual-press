<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Translation\Metabox\GenericMetaboxInfo;
use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxInfoDecorator;
use Inpsyde\MultilingualPress\Translation\Metabox\PriorityAwareMetaboxInfo;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class TranslationMetaboxInfo implements PriorityAwareMetaboxInfo {

	use MetaboxInfoDecorator;

	const ID_PREFIX = 'inpsyde_multilingual_';

	/**
	 * @var string
	 */
	private $context = 'advanced';

	/**
	 * @var string
	 */
	private $priority = 'default';

	/**
	 * Constructor.
	 *
	 * @param int      $site_id
	 * @param string   $language
	 * @param array    $post_types
	 * @param \WP_Post $post
	 */
	public function __construct( int $site_id, string $language, array $post_types, \WP_Post $post = null ) {

		$info = new GenericMetaboxInfo(
			self::ID_PREFIX . $site_id,
			$this->metabox_title( $site_id, $language, $post ),
			$post_types,
			$this->context,
			$this->priority
		);

		$this->decorate_metabox_info( $info );
	}

	/**
	 * Create the title for each metabox.
	 *
	 * @param int      $site_id
	 * @param string   $language
	 *
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	private function metabox_title( int $site_id, string $language, \WP_Post $post = null ) {

		/* translators: 1: site name, 2: language */
		$text = esc_html__( 'Translation for %1$s (%2$s)', 'multilingualpress' );

		$site_name = get_blog_option( $site_id, 'blogname' );
		$title     = sprintf( $text, $site_name, $language );

		if ( ! $post ) {
			// there's no remote post
			return $title;
		}

		switch_to_blog( $site_id );
		$title .= $this->edit_post_link( $post );
		restore_current_blog();

		return $title;
	}

	/**
	 * Used for the metabox title.
	 *
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	private function edit_post_link( \WP_Post $post ) {

		return sprintf(
			' <small> - <a href="%s">%s</a></small>',
			esc_url( get_edit_post_link( $post ) ),
			esc_html( $this->translated_status( $post ) )
		);
	}

	/**
	 * Status and, if available, publishing time.
	 *
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	private function translated_status( \WP_Post $post ) {

		$existing_statuses = get_post_statuses();

		$status = get_post_status( $post );

		$translated_status = $existing_statuses[ $status ] ?? ucfirst( $status );

		if ( in_array( $status, [ 'publish', 'private' ], true ) ) {

			$template = _x( '%1$s (%2$s)', '1 = post status, 2 = publish time', 'multilingualpress' );

			$post_time = get_post_time( get_option( 'date_format' ), false, $post );

			$translated_status = sprintf( $template, $translated_status, $post_time );
		}

		return $translated_status;
	}
}