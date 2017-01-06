<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_advanced_translator', 9 );

/**
 * Init the advanced translator.
 * @return void
 */
function mlp_feature_advanced_translator() {
	new Mlp_Advanced_Translator();
}