<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages\HTMLLinkTags;
use Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages\HTTPHeaders;
use Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages\Translations;
use Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages\UnfilteredTranslations;

_deprecated_file(
	'Mlp_Hreflang_Header_Output',
	'3.0.0',
	'Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages\HTMLLinkTags'
);

_deprecated_file(
	'Mlp_Hreflang_Header_Output',
	'3.0.0',
	'Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages\HTTPHeaders'
);

/**
 * @deprecated 3.0.0 Deprecated in favor of {@see HTMLLinkTags} and {@see HTTPHeaders}.
 */
class Mlp_Hreflang_Header_Output {

	/**
	 * @var Translations
	 */
	private $translations;

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see HTMLLinkTags} and {@see HTTPHeaders}.
	 *
	 * @param Mlp_Language_Api_Interface $language_api Language API object.
	 */
	public function __construct( Mlp_Language_Api_Interface $language_api ) {

		$this->translations = new UnfilteredTranslations( $language_api );
	}

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see HTTPHeaders::send}.
	 *
	 * @return void
	 */
	public function http_header() {

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages\HTTPHeaders::send'
		);

		( new HTTPHeaders( $this->translations ) )->send();
	}

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see HTMLLinkTags::render}.
	 *
	 * @return void
	 */
	public function wp_head() {

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages\HTMLLinkTags::render'
		);

		( new HTMLLinkTags( $this->translations ) )->render();
	}
}
