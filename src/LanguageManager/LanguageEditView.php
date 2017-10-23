<?php # -*- coding: utf-8 -*-
declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\Common\Type\NullLanguage;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

final class LanguageEditView {
	/**
	 * @var \Inpsyde\MultilingualPress\API\Languages
	 */
	private $languages;

	/**
	 * LanguageEditView constructor.
	 *
	 * @param Languages $languages
	 */
	public function __construct( Languages $languages )
	{
		$this->languages = $languages;
	}

	/**
	 * @param string $langID
	 *
	 * @return void
	 */
	public function render( string $langID )
	{
		$language = $this->languages->get_language_by( LanguagesTable::COLUMN_ID, $langID );

		if ( is_a( $language, NullLanguage::class ) ) {
			// now what?
			return;
		}

		// temporary
		print '<pre>' . print_r( $language, true ) . '</pre>';
	}
}