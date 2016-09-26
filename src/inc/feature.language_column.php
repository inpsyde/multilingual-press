<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Admin\SitesListTableColumn;

if ( is_admin() && ! empty( $GLOBALS[ 'pagenow' ] ) && 'sites.php' === $GLOBALS[ 'pagenow' ] ) {
	add_action( 'inpsyde_mlp_loaded', 'mlp_feature_language_column' );
}

/**
 * Set values and include at the hooks
 *
 * @return void
 */
function mlp_feature_language_column() {

	( new SitesListTableColumn(
		'multilingualpress.site_language',
		__( 'Site Language', 'multilingual-press' ),
		function ( $id, $site_id ) {

			switch_to_blog( $site_id );
			$language = (array) Mlp_Helpers::get_current_blog_language();
			restore_current_blog();

			return '' === $language
				? __( 'none', 'multilingual-press' )
				: '<div class="mlp_site_language">' . esc_html( Mlp_Helpers::get_lang_by_iso( $language ) ) . '</div>';
		}
	) )->register();
}
