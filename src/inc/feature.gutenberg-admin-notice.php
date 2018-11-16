<?php # -*- coding: utf-8 -*-

if ( ! is_admin() ) {
	return;
}

add_action( 'inpsyde_mlp_loaded', 'mlp_gutenberg_admin_notice' );

function mlp_gutenberg_admin_notice() {

	if ( current_user_can( 'manage_network_options' ) ) {

		add_action( 'network_admin_notices', 'mlp_render_gutenberg_notice' );

		Mlp_Dismissible_Notice::register(
			true,
			'mlp_gutenberg_notice',
			'manage_network_options'
		);
	}
}

function mlp_render_gutenberg_notice() {

	$notice = new Mlp_Dismissible_Notice( true );
	if ( $notice->is_dismissed( 'mlp_gutenberg_notice' ) ) {
		return;
	}

	$dismiss_url = $notice::dismiss_action_url(
		'mlp_gutenberg_notice',
		Mlp_Dismissible_Notice::ACTION_FOR_USER
	);
	?>

	<div class="notice notice-error is-dismissible" data-action="mlp_gutenberg_action_dismiss_notice"
		data-url="<?= esc_url( $dismiss_url ) ?>">
		<p>WARNING: MultilingualPress 2 is not compatible with Gutenberg. BEFORE you update your WordPress to version 5.0 please read our
			<a href="https://multilingualpress.org/docs/multilingualpress-wordpress-5-0-gutenberg/"
				target="_blank">MultilingualPress and WordPress 5.0 guide</a>
	</div>
	<script>
		(function( $ ) {
			setTimeout( function() {
				$( '.notice-dismiss' ).on( 'click', function( e ) {
					if ( $( this ).parent().data( 'action' ) === 'mlp_gutenberg_action_dismiss_notice' ) {
						e.preventDefault();
						$.post( $( this ).parent().data( 'url' ), { isAjax: 1 } );
					}
				} );
			}, 1000 );
		})( jQuery );
	</script>
<?php }

