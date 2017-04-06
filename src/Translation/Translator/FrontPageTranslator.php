<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Translator;

use Inpsyde\MultilingualPress\Factory\TypeFactory;

/**
 * Translator implementation for front-page requests.
 *
 * @package Inpsyde\MultilingualPress\Translation\Translator
 * @since   3.0.0
 */
final class FrontPageTranslator implements Translator {

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

		return [
			'remote_url' => $this->type_factory->create_url( [
				get_home_url( $site_id, '/' ),
			] ),
		];
	}
}
