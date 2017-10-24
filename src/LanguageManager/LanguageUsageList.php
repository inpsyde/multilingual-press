<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\Common\Type\Language;

use function Inpsyde\MultilingualPress\get_available_languages;

class LanguageUsageList {

	const ACTIVE   = 1;
	const INACTIVE = 2;

	/**
	 * @var \Inpsyde\MultilingualPress\API\Languages
	 */
	private $languages;

	/**
	 * Languages separated by their usage status.
	 *
	 * @var array
	 */
	private $separated = [];

	/**
	 * LanguageUsageList constructor.
	 *
	 * @param \Inpsyde\MultilingualPress\API\Languages $languages
	 */
	public function __construct( Languages $languages )
	{
		$this->languages = $languages;
	}

	/**
	 * Get either active or inactive languages.
	 *
	 * @param int $type
	 *
	 * @return array
	 */
	public function get_by( int $type ) : array
	{
		if ( empty ( $this->separated ) ) {
			$this->separate();
		}

		return $this->separated[ $type ] ?? [];
	}

	/**
	 * Separates languages by their usage status
	 *
	 * @return void
	 */
	private function separate()
	{
		// Prepare array. Once this has been done, $this->separated cannot be
		// empty anymore, so get_by() won't run this twice, even if there are
		// no languages available at all.
		$this->separated = [ self::ACTIVE => [], self::INACTIVE => [] ];

		$languages = $this->languages->get_all_languages();

		$available_languages = array_map( function ( $language ) {
			return str_replace( '_', '-', $language );
		}, get_available_languages( false ) );

		$this->separated = array_reduce( $languages, function ( array $languages, Language $language ) use (
			$available_languages
		) {
			$type = in_array( $language[ Language::HTTP_CODE ], $available_languages, true )
				? self::ACTIVE
				: self::INACTIVE;

			$languages[ $type ][] = $language;

			return $languages;
		}, [] );
	}
}