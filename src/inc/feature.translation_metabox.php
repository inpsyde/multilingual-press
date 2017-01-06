<?php # -*- coding: utf-8 -*-

add_action( 'inpsyde_mlp_loaded', 'mlp_feature_translation_metabox' );

/**
 * @return void
 */
function mlp_feature_translation_metabox() {

	new Mlp_Translation_Metabox();
}
