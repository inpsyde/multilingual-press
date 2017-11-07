<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Module settings tab view.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class ModuleSettingsTabView implements SettingsPageView {

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
	 */
	public function __construct( ModuleManager $module_manager, Nonce $nonce ) {

		$this->module_manager = $module_manager;

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

		?>
		<table class="widefat mlp-settings-table mlp-module-settings">
			<?php
			foreach ( $this->module_manager->get_modules() as $id => $module ) {
				/**
				 * Filters if the module should be listed on the settings page.
				 *
				 * @since 3.0.0
				 *
				 * @param bool $show_module Whether or not the module should be listed on the settings page.
				 */
				if ( apply_filters( "multilingualpress.show_module_{$id}", true ) ) {
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

		nonce_field( $this->nonce );
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

		$name = ModuleSettingsUpdater::NAME_MODULE_SETTINGS . '[' . $module->id() . ']';

		$id = 'multilingualpress-module-' . $module->id();
		?>
		<tr class="<?php echo esc_attr( $is_active ? 'active' : 'inactive' ); ?>">
			<th class="check-column" scope="row">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>"<?php checked( $is_active ); ?>>
			</th>
			<td>
				<label for="<?php echo esc_attr( $id ); ?>" class="mlp-block-label">
					<strong class="mlp-module-name"><?php echo esc_html( $module->name() ); ?></strong>
					<?php echo esc_html( $module->description() ); ?>
				</label>
			</td>
		</tr>
		<?php
	}
}
