<?php # -*- coding: utf-8 -*-
if ( is_admin() && ! empty ( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'sites.php' ){
	add_action( 'inpsyde_mlp_loaded', 'mlp_feature_connection_column' );
	add_action( 'inpsyde_mlp_loaded', 'mlp_feature_language_column' );
}
	

/**
 * @return void
 */
function mlp_feature_connection_column() {

	$columns = new Mlp_Custom_Columns(
		array (
			'id'               => 'mlp_interlinked',
			'header'           => __( 'Relationships', 'multilingualpress' ),
			'content_callback' => 'mlp_render_related_blog_column'
		)
	);

	add_filter( 'wpmu_blogs_columns', array ( $columns, 'add_header' ) );
	add_action( 'manage_sites_custom_column', array ( $columns, 'render_column' ), 10, 2 );
}

/**
 * @return void
 */
function mlp_feature_language_column() {

	$columns = new Mlp_Custom_Columns(
		array (
			'id'               => 'mlp_site_language',
			'header'           => __( 'Site Language', 'multilingualpress' ),
			'content_callback' => 'mlp_render_site_language_column'
		)
	);

	add_filter( 'wpmu_blogs_columns', array ( $columns, 'add_header' ) );
	add_action( 'manage_sites_custom_column', array ( $columns, 'render_column' ), 10, 2 );
}

/**
 * @param  string $column_name not used
 * @param  int $blog_id
 * @return string|void
 */
function mlp_render_related_blog_column( $column_name, $blog_id ) {

	switch_to_blog( $blog_id );
	$blogs  = (array) mlp_get_available_languages_titles();
	restore_current_blog();

	unset ( $blogs[ $blog_id ] );

	if ( empty ( $blogs ) )
		return __( 'none', 'multilingualpress' );

	$blogs = array_map( 'esc_html', $blogs );

	return '<div class="mlp_interlinked_blogs"><b>' . join( '</b><br /><b>', $blogs ) . '</b></div>';
}

/**
 * @param  string $column_name not used
 * @param  int $blog_id
 * @return string|void
 */
function mlp_render_site_language_column( $column_name, $blog_id ) {

	switch_to_blog( $blog_id );
	$lang  = mlp_get_current_blog_language();
	restore_current_blog();


	if ( empty ( $lang ) )
		return __( 'none', 'multilingualpress' );
	$lang = Mlp_Helpers::get_lang_by_iso($lang);
	//$blogs = array_map( 'esc_html', $blogs );

	return '<div class="mlp_site_language"><b>' . $lang . '</b></div>';
}