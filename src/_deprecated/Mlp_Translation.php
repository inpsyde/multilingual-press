<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\FilterableTranslation;
use Inpsyde\MultilingualPress\Common\Type\Language;

_deprecated_file(
	'Mlp_Translation',
	'3.0.0',
	'Inpsyde\MultilingualPress\Common\Type\FilterableTranslation'
);

/**
 * @deprecated 3.0.0 Deprecated in favor of {@see FilterableTranslation}.
 */
class Mlp_Translation extends FilterableTranslation {

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see FilterableTranslation::__construct}.
	 *
	 * @param array    $args     Translation arguments.
	 * @param Language $language Language object.
	 */
	public function __construct( array $args, Language $language ) {

		if ( empty( $args['icon_url'] ) && isset( $args['icon'] ) ) {
			$args['icon_url'] = $args['icon'];

			_deprecated_argument(
				__METHOD__,
				'3.0.0',
				'The key "icon" of the $args parameter is deprecated. Please use "icon_url" instead.'
			);

			unset( $args['icon'] );
		}

		if ( empty( $args['remote_title'] ) && isset( $args['target_title'] ) ) {
			$args['remote_title'] = $args['target_title'];

			_deprecated_argument(
				__METHOD__,
				'3.0.0',
				'The key "target_title" of the $args parameter is deprecated. Please use "remote_title" instead.'
			);

			unset( $args['target_title'] );
		}

		if ( empty( $args['remote_url'] ) && isset( $args['target_url'] ) ) {
			$args['remote_url'] = $args['target_url'];

			_deprecated_argument(
				__METHOD__,
				'3.0.0',
				'The key "target_url" of the $args parameter is deprecated. Please use "remote_url" instead.'
			);

			unset( $args['target_url'] );
		}

		if ( empty( $args['type'] ) && isset( $args['page_type'] ) ) {
			$args['type'] = $args['page_type'];

			_deprecated_argument(
				__METHOD__,
				'3.0.0',
				'The key "page_type" of the $args parameter is deprecated. Please use "type" instead.'
			);

			unset( $args['page_type'] );
		}

		parent::__construct( $args, $language );
	}
}
