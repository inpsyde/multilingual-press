<?php # -*- coding: utf-8 -*-
/**
 * Add each site language to network site table
 *
 * @since   2015-06-11
 * @version 2015-06-11
 */

if ( is_admin() && ! empty( $GLOBALS['pagenow'] ) && 'sites.php' === $GLOBALS['pagenow'] ) {
	add_action( 'inpsyde_mlp_loaded', 'mlp_feature_language_column' );
}

/**
 * Set values and include at the hooks
 *
 * @return void
 */
function mlp_feature_language_column() {

	$columns = new Mlp_Custom_Columns(
		array(
			'id'               => 'mlp_site_language',
			'header'           => esc_attr__( 'Site Language', 'multilingual-press' ),
			'content_callback' => 'mlp_render_site_language_column',
		)
	);

	add_filter( 'wpmu_blogs_columns', array( $columns, 'add_header' ) );
	add_action( 'manage_sites_custom_column', array( $columns, 'render_column' ), 10, 2 );
}

/**
 * Get markup and language title for each site
 *
 * @param  string $column_name not used
 * @param  int    $blog_id
 *
 * @return string
 */
function mlp_render_site_language_column(
	/** @noinspection PhpUnusedParameterInspection */
	$column_name, $blog_id
) {

	switch_to_blog( $blog_id );
	$lang = Mlp_Helpers::get_current_blog_language();
	restore_current_blog();

	if ( empty( $lang ) ) {
		return esc_html__( 'none', 'multilingual-press' );
	}

	$lang = Mlp_Helpers::get_lang_by_iso( $lang );

	return '<div class="mlp_site_language">' . esc_html( $lang ) . '</div>';
}
