<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;

/**
 * Plugin settings page view.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class PluginSettingsPageView implements SettingsPageView {

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var ModuleManager
	 */
	private $module_manager;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 * @param Nonce         $nonce          Nonce object.
	 * @param AssetManager  $asset_manager  Asset manager object.
	 */
	public function __construct( ModuleManager $module_manager, Nonce $nonce, AssetManager $asset_manager ) {

		$this->module_manager = $module_manager;

		$this->nonce = $nonce;

		$this->asset_manager = $asset_manager;
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

		$action = PluginSettingsUpdater::ACTION;
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="<?php echo admin_url( "admin-post.php?action={$action}" ); ?>"
				id="multilingualpress-modules">
				<?php echo \Inpsyde\MultilingualPress\nonce_field( $this->nonce ); ?>
				<table class="mlp-module-list">
					<?php
					foreach ( $this->module_manager->get_modules() as $id => $module ) {
						/**
						 * Filters if the module should be listed on the settings page.
						 *
						 * @since 3.0.0
						 *
						 * @param bool $show_module Whether or not the module should be listed on the settings page.
						 */
						if ( apply_filters( "multilingualpress.show_module_$id", true ) ) {
							$this->render_module( $module );
						}
					}

					/**
					 * Fires at the end of but still inside the module list on the settings page.
					 *
					 * @since 3.0.0
					 */
					do_action( 'multilingualpress.in_module_list' );
					?>
				</table>
				<?php
				/**
				 * Fires right after after the module list on the settings page.
				 *
				 * @since 3.0.0
				 */
				do_action( 'multilingualpress.after_module_list' );

				submit_button( __( 'Save Changes', 'multilingual-press' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders the markup for the given module.
	 *
	 * @param Module $module Module object.
	 *
	 * @return void
	 */
	private function render_module( Module $module ) {

		$is_active = $module->is_active();

		$id = 'multilingualpress-module-' . $module->id();
		?>
		<tr class="<?php echo esc_attr( $is_active ? 'active' : 'inactive' ); ?>">
			<td class="check-column">
				<input type="checkbox"
					name="<?php echo esc_attr( 'multilingualpress_modules[' . $module->id() . ']' ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>"<?php checked( $is_active ); ?>>
			</td>
			<td>
				<label for="<?php echo esc_attr( $id ); ?>" class="mlp-block-label">
					<strong><?php echo esc_html( $module->name() ); ?></strong>
					<?php echo wpautop( esc_html( $module->description() ) ); ?>
				</label>
			</td>
		</tr>
		<?php
	}
}
