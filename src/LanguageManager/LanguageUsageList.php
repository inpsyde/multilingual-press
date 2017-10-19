<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\API\Languages;
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
	public function get_by( int $type )
	{
		if ( empty ( $this->separated ) ) {
			$this->separate();
		}

		if ( ! empty ( $this->separated[ $type ] ) ) {
			return $this->separated[ $type ];
		}

		return [];
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

		$active = get_available_languages( false );
		$active = array_map( function( $val ) {
			return str_replace( '_', '-', $val );
		}, $active );

		$languages = $this->languages->get_all_languages();

		foreach ( $languages as $language ) {
			if ( $language->offsetExists( 'http_code' )
				and ( in_array( $language->offsetGet( 'http_code' ), $active ) )
			) {
				$this->separated[ self::ACTIVE ][] = $language;
			}
			else {
				$this->separated[ self::INACTIVE ][] = $language;
			}
		}
	}
}