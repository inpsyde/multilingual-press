<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Admin\SitesListTableColumn;

if ( is_admin() && ! empty( $GLOBALS[ 'pagenow' ] ) && 'sites.php' === $GLOBALS[ 'pagenow' ] ) {
	add_action( 'inpsyde_mlp_loaded', 'mlp_feature_connection_column' );
}

/**
 * Set values and include at the hooks
 *
 * @return void
 */
function mlp_feature_connection_column() {

	( new SitesListTableColumn(
		'multilingualpress.relationships',
		__( 'Relationships', 'multilingual-press' ),
		function ( $id, $site_id ) {

			switch_to_blog( $site_id );
			$sites = (array) Mlp_Helpers::get_available_languages_titles();
			restore_current_blog();
			unset( $sites[ $site_id ] );

			return $sites
				? '<div class="mlp_interlinked_blogs">' . join( '<br>', array_map( 'esc_html', $sites ) ) . '</div>'
				: __( 'none', 'multilingual-press' );
		}
	) )->register();
}
