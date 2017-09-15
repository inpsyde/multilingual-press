<?php # -*- coding: utf-8 -*-

if ( ! is_admin() ) {
	return;
}

add_action( 'inpsyde_mlp_loaded', 'mlp_register_become_inpsyder_admin_notice' );

function mlp_register_become_inpsyder_admin_notice() {

	if ( current_user_can( 'manage_network_options' ) ) {
		add_action( 'network_admin_notices', array( new Mlp_Become_Inpsyder_Admin_Notice(), 'render' ) );

		Mlp_Dismissible_Notice::register(
			true,
			Mlp_Become_Inpsyder_Admin_Notice::NOTICE_ID,
			'manage_network_options'
		);
	}
}

class Mlp_Become_Inpsyder_Admin_Notice {

	const NOTICE_ID = 'become_inpsyder';

	/**
	 * @var string[]
	 */
	private $screen_ids = array(
		'settings_page_language-manager-network',
		'settings_page_mlp-network',
	);

	/**
	 * @var bool
	 */
	private static $should_display;

	public function render() {

		static $done;

		if ( ! $done && in_array( get_current_screen()->id, $this->screen_ids, true ) && $this->should_display() ) {
			$done = true;
			?>
			<div class="metabox-holder postbox" id="mlp-become-inpsyder-admin-notice">
				<h3 class="hndle">
					<span><?php esc_html_e( 'We Want You for MultilingualPress!', 'multilingual-press' ); ?></span>
				</h3>
				<div class="inside">
					<?php $this->render_content(); ?>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * @return bool
	 */
	private function should_display() {

		if ( ! isset( self::$should_display ) ) {
			$notice = new Mlp_Dismissible_Notice( true );

			self::$should_display = ! $notice->is_dismissed( self::NOTICE_ID );
		}

		return self::$should_display;
	}

	private function render_content() {

		$dismiss_url = Mlp_Dismissible_Notice::dismiss_action_url(
			self::NOTICE_ID,
			Mlp_Dismissible_Notice::ACTION_FOR_USER
		);

		$logo_url = plugins_url( '/assets/images/inpsyde.png', dirname( dirname( __FILE__ ) ) . '/multilingual-press.php' );

		$job_url = __(
			'https://inpsyde.com/en/jobs/?utm_source=MultilingualPress&utm_medium=Link&utm_campaign=BecomeAnInpsyder',
			'multilingual-press'
		);
		?>
		<div>
			<p align="justify">
				<?php
				esc_html_e(
					'We want to make MultilingualPress even better and its support much faster.',
					'multilingual-press'
				);
				?>
				<br>
				<?php
				/* translators: 1: opening <strong> tag, 2: closing </strong> tag */
				$message = __(
					'%sThis is why we are looking for a talented developer who can work remotely and support us in anything MultilingualPress%s, and other exciting WordPress projects at our VIP partner agency.',
					'multilingual-press'
				);

				$tags = array(
					'strong' => array(),
				);
				echo wp_kses( sprintf( $message, '<strong>', '</strong>' ), $tags );
				?>
			</p>
			<p>
				<a
					style="background: #9FC65D; border-color: #7ba617 #719c0d #719c0d; -webkit-box-shadow: 0 1px 0 #719c0d; box-shadow: 0 1px 0 #719c0d; text-shadow: 0 -1px 1px #719c0d, 1px 0 1px #719c0d, 0 1px 1px #719c0d, -1px 0 1px #719c0d;"
					class="button button-large button-primary"
					href="<?php echo esc_url( $job_url ); ?>"
					target="_blank">
					<?php esc_html_e( 'Apply now!', 'multilingual-press' ); ?>
				</a>
			</p>
			<hr>
			<p>
				<a class="button button-small" id="mlp-become-inpsyder-admin-notice-dismiss-button"
					href="<?php echo esc_url( $dismiss_url ); ?>">
					<?php esc_html_e( "Don't show again!", 'multilingual-press' ); ?>
				</a>

				<a style="float: right;" href="<?php echo esc_url( $job_url ); ?>">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Work at Inpsyde', 'multilingual-press' ); ?>">
				</a>
			</p>
		</div>
		<script>
			(function( $ ) {
					$( '#mlp-become-inpsyder-admin-notice-dismiss-button' ).on( 'click', function( e ) {
						e.preventDefault();
						$.post( $( this ).attr( 'href' ), { isAjax: 1 } );
						$( '#mlp-become-inpsyder-admin-notice' ).hide();
					} );
				})( jQuery );
		</script>
		<?php
	}
}
