<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\GenericMetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxDecorator;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\PriorityAwareMetaBox;
use Inpsyde\MultilingualPress\Translation\Post\ActivePostTypes;

use function Inpsyde\MultilingualPress\get_site_language;

/**
 * Meta box implementation for post translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox
 * @since   3.0.0
 */
final class TranslationMetaBox implements PriorityAwareMetaBox {

	use MetaBoxDecorator;

	/**
	 * ID prefix.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ID_PREFIX = 'mlp_post_translation_meta_box_';

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int             $site_id    Site ID.
	 * @param ActivePostTypes $post_types Active post types object.
	 * @param \WP_Post        $post       Optional. Post object. Defaults to null.
	 */
	public function __construct( int $site_id, ActivePostTypes $post_types, \WP_Post $post = null ) {

		$this->decorate_meta_box( new GenericMetaBox(
			self::ID_PREFIX . $site_id,
			$this->get_title_for_site( $site_id, $post ),
			$post_types->names()
		) );
	}

	/**
	 * Returns the meta box title for the site with the given ID.
	 *
	 * @param int      $site_id Site ID.
	 * @param \WP_Post $post    Optional. Post object. Defaults to null.
	 *
	 * @return string Meta box title.
	 */
	private function get_title_for_site( int $site_id, \WP_Post $post = null ) {

		/* translators: 1: site name, 2: language */
		$text = __( 'Translation for %1$s (%2$s)', 'multilingualpress' );

		$title = sprintf(
			$text,
			get_blog_option( $site_id, 'blogname' ),
			get_site_language( $site_id ) ?: $site_id
		);

		if ( $post ) {
			switch_to_blog( $site_id );

			/* translators: 1: meta box title, 2: edit link */
			$template = _x( '%1$s<small> - %2$s</small>', 'Translation meta box title', 'multilingualpress' );

			$edit_post_link = sprintf(
				'<a href="%2$s">%1$s</a>',
				esc_html( $this->get_translated_post_status( $post ) ),
				esc_url( get_edit_post_link( $post ) )
			);

			$title = sprintf(
				$template,
				esc_html( $title ),
				$edit_post_link
			);

			restore_current_blog();
		}

		return $title;
	}

	/**
	 * Status and, if available, publishing time.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return string
	 */
	private function get_translated_post_status( \WP_Post $post ) {

		$post_statuses = get_post_statuses();

		$status = get_post_status( $post );

		$translated_status = $post_statuses[ $status ] ?? ucfirst( $status );

		if ( ! in_array( $status, [ 'publish', 'private' ], true ) ) {
			return $translated_status;
		}

		/* translators: 1: post status, 2: publish time */
		$template = _x( '%1$s (%2$s)', 'Post status for display in translation meta box title', 'multilingualpress' );

		return sprintf(
			$template,
			$translated_status,
			get_post_time( get_option( 'date_format' ), false, $post )
		);
	}
}
