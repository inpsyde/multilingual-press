<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\Common\Labels;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

final class LanguageMetaLabels implements Labels
{
	/**
	 * @var array
	 */
	private $labels;

	public function __construct(  )
	{
		$this->labels = [
			LanguagesTable::COLUMN_NATIVE_NAME    => __( 'Native name', 'multilingualpress' ),
			LanguagesTable::COLUMN_ENGLISH_NAME   => __( 'English name', 'multilingualpress' ),
			LanguagesTable::COLUMN_RTL            => __( 'RTL', 'multilingualpress' ),
			LanguagesTable::COLUMN_HTTP_CODE      => __( 'HTTP', 'multilingualpress' ),
			LanguagesTable::COLUMN_ISO_639_1_CODE => __( 'ISO&#160;639-1', 'multilingualpress' ),
			LanguagesTable::COLUMN_LOCALE         => __( 'Locale', 'multilingualpress' ),
			LanguagesTable::COLUMN_PRIORITY       => __( 'Priority', 'multilingualpress' ),
		];
	}

	public function label( string $name ) : string
	{
		if ( isset ( $this->labels[ $name ] ) ) {
			return $this->labels[ $name ];
		}

		return '';
	}

	public function all() : array
	{
		return $this->labels;
	}
}