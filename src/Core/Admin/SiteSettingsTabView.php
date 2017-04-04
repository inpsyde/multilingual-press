<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\Admin\EditSiteTabData;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingView;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Settings page view for the MultilingualPress site settings tab.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class SiteSettingsTabView implements SettingsPageView {

	/**
	 * @var EditSiteTabData
	 */
	private $data;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var SiteSettingView
	 */
	private $view;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param EditSiteTabData $data  Tab data object.
	 * @param SiteSettingView $view  Site settings view object.
	 * @param Request         $request
	 * @param Nonce           $nonce Nonce object.
	 */
	public function __construct( EditSiteTabData $data, SiteSettingView $view, Request $request, Nonce $nonce ) {

		$this->data = $data;

		$this->view = $view;

		$this->request = $request;

		$this->nonce = $nonce;
	}

	/**
	 * Renders the markup.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render() {

		$site_id = (int) $this->request->body_value( 'id', INPUT_REQUEST, FILTER_SANITIZE_NUMBER_INT );
		if ( ! $site_id ) {
			wp_die( __( 'Invalid site ID.', 'multilingualpress' ) );
		}

		$site = get_site( $site_id );
		if ( ! $site ) {
			wp_die( __( 'The requested site does not exist.', 'multilingualpress' ) );
		}
		?>
		<div class="wrap">
			<?php $this->render_head( $site ); ?>
			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
				<input type="hidden" name="action"
					value="<?php echo esc_attr( SiteSettingsUpdateRequestHandler::ACTION ); ?>">
				<input type="hidden" name="id" value="<?php echo esc_attr( $site_id ); ?>">
				<?php echo nonce_field( $this->nonce ); ?>
				<table class="form-table mlp-admin-settings-table">
					<?php $this->view->render( $site_id ); ?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders the title, action links and the tabs.
	 *
	 * @param \WP_Site $site Site object.
	 *
	 * @return void
	 */
	private function render_head( \WP_Site $site ) {

		$site_id = $site->id;

		/* translators: %s: site name */
		$title = sprintf( __( 'Edit Site: %s', 'multilingualpress' ), $site->blogname );
		?>
		<h1 id="edit-site"><?php echo esc_html( $title ); ?></h1>
		<?php settings_errors(); ?>
		<p class="edit-site-actions">
			<a href="<?php echo esc_url( get_home_url( $site_id, '/' ) ); ?>">
				<?php _e( 'Visit', 'multilingualpress' ); ?>
			</a>
			|
			<a href="<?php echo esc_url( get_admin_url( $site_id ) ); ?>">
				<?php _e( 'Dashboard', 'multilingualpress' ); ?>
			</a>
		</p>
		<?php
		network_edit_site_nav( [
			'blog_id'  => $site_id,
			'selected' => $this->data->id(),
		] );
		?>
		<?php
	}
}
