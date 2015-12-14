<?php # -*- coding: utf-8 -*-
/**
 * Add related sites to each site in the network site view
 *
 * @version  2015-06-11
 */
if ( is_admin() && ! empty( $GLOBALS[ 'pagenow' ] ) && 'sites.php' === $GLOBALS[ 'pagenow' ] ) {
	add_action( 'inpsyde_mlp_loaded', 'mlp_feature_connection_column' );
}

/**
 * Set values and include at the hooks
 *
 * @return void
 */
function mlp_feature_connection_column() {

	$columns = new Mlp_Custom_Columns(
		array(
			'id'               => 'mlp_interlinked',
			'header'           => esc_attr__( 'Relationships', 'multilingual-press' ),
			'content_callback' => 'mlp_render_related_blog_column',
		)
	);

	add_filter( 'wpmu_blogs_columns', array( $columns, 'add_header' ) );
	add_action( 'manage_sites_custom_column', array( $columns, 'render_column' ), 10, 2 );
}

/**
 * Add related site title for each site to network site view
 *
 * @param  string $column_name not used
 * @param  int    $blog_id
 *
 * @return string
 */
function mlp_render_related_blog_column(
	/** @noinspection PhpUnusedParameterInspection */
	$column_name, $blog_id
) {

	switch_to_blog( $blog_id );
	$blogs = (array) Mlp_Helpers::get_available_languages_titles();
	restore_current_blog();

	unset( $blogs[ $blog_id ] );

	if ( empty( $blogs ) ) {
		return esc_html__( 'none', 'multilingual-press' );
	}

	$blogs = array_map( 'esc_html', $blogs );

	return '<div class="mlp_interlinked_blogs">' . join( '<br>', $blogs ) . '</div>';
}
