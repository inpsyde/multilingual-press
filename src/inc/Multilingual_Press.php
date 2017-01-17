<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\MultilingualPress;

/**
 * Class Multilingual_Press
 */
class Multilingual_Press {

	/**
	 * @return bool
	 */
	public function setup() {

		// Advanced Translator
		new Mlp_Advanced_Translator();

		// Translation Meta Box
		new Mlp_Translation_Metabox();

		if ( is_admin() ) {
			// Term Translator
			add_action( 'wp_loaded', function () {

				$taxonomy = empty( $_REQUEST['taxonomy'] ) ? '' : (string) $_REQUEST['taxonomy'];

				$term_taxonomy_id = empty( $_REQUEST['tag_ID'] ) ? 0 : (int) $_REQUEST['tag_ID'];

				( new Mlp_Term_Translation_Controller(
					MultilingualPress::resolve( 'multilingualpress.content_relations' ),
					new WPNonce( "save_{$taxonomy}_translations_$term_taxonomy_id" )
				) )->setup();
			}, 0 );

			// Site Settings
			$setting = new Mlp_Network_Site_Settings_Tab_Data(
				MultilingualPress::resolve( 'multilingualpress.type_factory' )
			);

			new Mlp_Network_Site_Settings_Controller( $setting, new WPNonce( $setting->action() ) );

			new Mlp_Network_New_Site_Controller(
				MultilingualPress::resolve( 'multilingualpress.site_relations' ),
				MultilingualPress::resolve( 'multilingualpress.languages' )
			);
		}

		return true;
	}

	/**
	 * @return void
	 */
	public function prepare_plugin_data() {

		new Mlp_Language_Manager_Controller(
			new Mlp_Language_Db_Access( MultilingualPress::resolve( 'multilingualpress.languages_table' )->name() ),
			MultilingualPress::resolve( 'multilingualpress.wpdb' )
		);
	}
}
