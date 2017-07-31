<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\GenericMetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxDecorator;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBox;
use Inpsyde\MultilingualPress\Translation\Term\ActiveTaxonomies;

use function Inpsyde\MultilingualPress\get_site_language;

/**
 * Meta box implementation for term translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term\MetaBox
 * @since   3.0.0
 */
final class TranslationMetaBox implements MetaBox {

	use MetaBoxDecorator;

	/**
	 * ID prefix.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ID_PREFIX = 'mlp_term_translation_meta_box_';

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int              $site_id           Site ID.
	 * @param ActiveTaxonomies $active_taxonomies Active taxonomies object.
	 * @param \WP_Term         $term              Optional. Term object. Defaults to null.
	 */
	public function __construct( int $site_id, ActiveTaxonomies $active_taxonomies, \WP_Term $term = null ) {

		$this->decorate_meta_box( new GenericMetaBox(
			self::ID_PREFIX . $site_id,
			$this->get_title_for_site( $site_id, $term ),
			$this->allowed_screens( $active_taxonomies ),
			''
		) );
	}

	/**
	 * @param ActiveTaxonomies $active_taxonomies
	 *
	 * @return string[]
	 */
	private function allowed_screens( ActiveTaxonomies $active_taxonomies ) {

		return array_map( function ( string $taxonomy ) {

			return "edit-{$taxonomy}";
		}, $active_taxonomies->names() );

	}

	/**
	 * Returns the meta box title for the site with the given ID.
	 *
	 * @param int      $site_id Site ID.
	 * @param \WP_Term $term    Optional. Term object. Defaults to null.
	 *
	 * @return string Meta box title.
	 */
	private function get_title_for_site( int $site_id, \WP_Term $term = null ) {

		/* translators: 1: site name, 2: language */
		$text = __( 'Translation for %1$s (%2$s)', 'multilingualpress' );

		$title = sprintf(
			$text,
			get_blog_option( $site_id, 'blogname' ),
			get_site_language( $site_id ) ?: $site_id
		);

		if ( $term ) {
			switch_to_blog( $site_id );

			/* translators: 1: meta box title, 2: term edit link */
			$template = _x( '%1$s <small>%2$s</small>', 'Term translation meta box title', 'multilingualpress' );

			$edit_term_link = sprintf(
				'<a href="%2$s">%1$s</a>',
				esc_html_x( 'edit', 'Term translation meta box', 'multilingualpress' ),
				esc_url( get_edit_term_link( $term->term_id, $term->taxonomy ) )
			);

			$title = sprintf(
				$template,
				esc_html( $title ),
				$edit_term_link
			);

			restore_current_blog();
		}

		return $title;
	}
}
