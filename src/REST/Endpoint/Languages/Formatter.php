<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Endpoint\Languages;

use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Factory\RESTResponseFactory;
use Inpsyde\MultilingualPress\REST\Common\Endpoint\Schema;
use Inpsyde\MultilingualPress\REST\Common\Response\DataAccess;
use Inpsyde\MultilingualPress\REST\Common\Response\DataFilter;

use function Inpsyde\MultilingualPress\rest_url;

/**
 * Language formatter implementation.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\Languages
 * @since   3.0.0
 */
class Formatter {

	/**
	 * @var DataAccess
	 */
	private $data_access;

	/**
	 * @var DataFilter
	 */
	private $data_filter;

	/**
	 * @var string
	 */
	private $object_type;

	/**
	 * @var RESTResponseFactory
	 */
	private $response_factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param DataFilter          $data_filter      Response data filter object.
	 * @param Schema              $schema           Endpoint schema object.
	 * @param RESTResponseFactory $response_factory Response factory object.
	 * @param DataAccess          $data_access      Response data access object.
	 */
	public function __construct(
		DataFilter $data_filter,
		Schema $schema,
		RESTResponseFactory $response_factory,
		DataAccess $data_access
	) {

		$this->data_filter = $data_filter;

		$this->object_type = $schema->title();

		$this->response_factory = $response_factory;

		$this->data_access = $data_access;
	}

	/**
	 * Returns a formatted representation of the given language data.
	 *
	 * @since 3.0.0
	 *
	 * @param Language[] $languages Array of language objects.
	 * @param string     $context   Request context.
	 *
	 * @return array[] The formatted representation of the given data.
	 */
	public function format( array $languages, string $context ) {

		$languages = array_filter( $languages, function ( $language ) {

			return $language instanceof Language;
		} );

		return array_reduce( $languages, function ( array $data, Language $language ) use ( $context ) {

			$data[] = $this->format_item( $language, $context );

			return $data;
		}, [] );
	}

	/**
	 * Filters the given response data and adds relevant links.
	 *
	 * @param Language $language Language object.
	 * @param string   $context  Request context.
	 *
	 * @return array The filtered response data with links.
	 */
	private function format_item( Language $language, string $context ) {

		$id = $language[ Language::ID ];

		$data = [
			'id'           => $id,
			'english_name' => $language[ Language::ENGLISH_NAME ],
			'native_name'  => $language[ Language::NATIVE_NAME ],
			'custom_name'  => $language[ Language::CUSTOM_NAME ],
			'rtl'          => $language[ Language::IS_RTL ],
			'iso_639_1'    => $language[ Language::ISO_639_1_CODE ],
			'iso_639_2'    => $language[ Language::ISO_639_2_CODE ],
			'locale'       => $language[ Language::LOCALE ],
			'http_code'    => $language[ Language::HTTP_CODE ],
			'priority'     => $language[ Language::PRIORITY ],
		];
		$data = $this->data_filter->filter_data( $data, $context );

		$response = $this->response_factory->create( [ $data ] );
		$response->add_links( [
			'self'       => [
				'href' => rest_url( "{$this->object_type}/{$id}" ),
			],
			'collection' => [
				'href' => rest_url( $this->object_type ),
			],
		] );

		return $this->data_access->get_data( $response );
	}
}
