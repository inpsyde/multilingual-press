<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageTab;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Plugin settings page view.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class PluginSettingsPageView implements SettingsPageView {

	/**
	 * Query argument name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const QUERY_ARG_TAB = 'tab';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var ServerRequest
	 */
	private $request;

	/**
	 * @var SettingsPageTab[]
	 */
	private $tabs;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Nonce             $nonce         Nonce object.
	 * @param AssetManager      $asset_manager Asset manager object.
	 * @param ServerRequest     $request       Server request object.
	 * @param SettingsPageTab[] $tabs          Array of settings page tab objects.
	 */
	public function __construct(
		Nonce $nonce,
		AssetManager $asset_manager,
		ServerRequest $request,
		array $tabs
	) {

		$this->nonce = $nonce;

		$this->asset_manager = $asset_manager;

		$this->request = $request;

		$this->tabs = array_filter( $tabs, function ( $tab ) {

			return $tab instanceof SettingsPageTab;
		} );
	}

	/**
	 * Renders the markup.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render() {

		$this->asset_manager->enqueue_style( 'multilingualpress-admin' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_form(); ?>
		</div>
		<?php
	}

	/**
	 * Returns the slug of the active tab.
	 *
	 * @return string
	 */
	private function get_active_tab() {

		static $active_tab;
		if ( ! isset( $active_tab ) ) {
			$tab = (string) $this->request->body_value( self::QUERY_ARG_TAB, INPUT_GET );

			$active_tab = $tab && array_key_exists( $tab, $this->tabs )
				? $tab
				: key( $this->tabs );
		}

		return $active_tab;
	}

	/**
	 * Renders the active tab content.
	 *
	 * @return void
	 */
	private function render_content() {

		$this->tabs[ $this->get_active_tab() ]->view()->render();
	}

	/**
	 * Renders the form.
	 *
	 * @return void
	 */
	private function render_form() {

		if ( ! $this->tabs ) {
			return;
		}

		$this->render_tabs();

		$url = admin_url( 'admin-post.php?action=' . PluginSettingsUpdater::ACTION )
		?>
		<form method="post" action="<?php echo esc_url( $url ); ?>" id="multilingualpress-modules">
			<?php
			nonce_field( $this->nonce );

			$this->render_content();

			submit_button( __( 'Save Changes', 'multilingualpress' ) );
			?>
		</form>
		<?php
	}

	/**
	 * Renders the tabbed navigation.
	 *
	 * @return void
	 */
	private function render_tabs() {

		?>
		<h2 class="nav-tab-wrapper wp-clearfix">
			<?php array_walk( $this->tabs, [ $this, 'render_tab' ], $this->get_active_tab() ); ?>
		</h2>
		<?php
	}

	/**
	 * Renders the given tab.
	 *
	 * @param SettingsPageTab $tab    Tab object.
	 * @param string          $slug   Tab slug.
	 * @param string          $active Active tab slug.
	 *
	 * @return void
	 */
	private function render_tab( SettingsPageTab $tab, string $slug, string $active ) {

		$url = add_query_arg( self::QUERY_ARG_TAB, $slug );

		$class = 'nav-tab';
		if ( $active === $slug ) {
			$class .= ' nav-tab-active';
		}
		?>
		<a href="<?php echo esc_url( $url ); ?>" id="<?php echo esc_attr( $tab->id() ); ?>"
			class="<?php echo esc_attr( $class ); ?>">
			<?php echo esc_html( $tab->title() ); ?>
		</a>
		<?php
	}
}
