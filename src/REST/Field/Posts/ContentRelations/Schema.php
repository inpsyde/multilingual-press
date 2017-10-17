<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Field\Posts\ContentRelations;

use Inpsyde\MultilingualPress\REST\Common;

/**
 * Content relations post field schema.
 *
 * @package Inpsyde\MultilingualPress\REST\Field\Posts\ContentRelations
 * @since   3.0.0
 */
final class Schema implements Common\Schema {

	/**
	 * Returns the schema definition.
	 *
	 * @since 3.0.0
	 *
	 * @return array Schema definition.
	 */
	public function definition(): array {

		return [
			'description' => __( 'An array of related post objects with site and content ID.', 'multilingualpress' ),
			'type'        => 'array',
			'context'     => [
				'view',
				'edit',
			],
		];
	}
}
