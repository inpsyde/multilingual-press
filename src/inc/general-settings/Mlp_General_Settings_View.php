<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Module\Module;

/**
 * Class Mlp_General_Settings_View
 *
 * Create UI for module settings.
 *
 * @version 2014.01.17
 * @author  toscho
 * @license GPL
 */
class Mlp_General_Settings_View {

	/**
	 * @var Mlp_Module_Mapper_Interface
	 */
	private $module_mapper;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param Mlp_Module_Mapper_Interface $module_mapper Module mapper.
	 */
	public function __construct( Mlp_Module_Mapper_Interface $module_mapper ) {

		$this->module_mapper = $module_mapper;
	}

	public function render_page() {

		?>
		<div class="wrap">
			<h1><?php echo esc_html( $GLOBALS['title'] ); ?></h1>
			<?php $this->modules_form(); ?>
		</div>
		<?php
	}

	/**
	 * Modules Manager
	 *
	 * @since	0.1
	 * @uses	get_site_option, _e, admin_url, wp_nonce_field,
	 * 			do_action, submit_button
	 * @return	void
	 */
	public function modules_form() {

		$modules = $this->module_mapper->get_modules();

		// Draw the form
		?>
		<form action="<?php echo admin_url( 'admin-post.php?action=mlp_update_modules' ); ?>" method="post"
			id="mlp_modules">
			<?php wp_nonce_field( $this->module_mapper->get_nonce_action() ); ?>
			<table class="mlp-admin-feature-table">
				<?php
				foreach ( $modules as $id => $module ) {
					/**
					 * Filter the visibility of the module in the features table.
					 *
					 * @param bool $invisible Should the module be hidden?
					 */
					if ( apply_filters( "mlp_dont_show_module_$id", false ) ) {
						continue;
					}

					echo $this->module_row( $module );
				}

				/**
				 * Runs at the end of but still inside the features table.
				 */
				do_action( 'mlp_modules_add_fields_to_table' );
				?>
			</table>
			<?php
			/**
			 * Runs after the features table.
			 */
			do_action( 'mlp_modules_add_fields' );
			submit_button( __( 'Save changes', 'multilingual-press' ) );
			?>
		</form>
		<?php
	}

	/**
	 * Create markup for activation rows.
	 *
	 * @param Module $module
	 * @return string
	 */
	protected function module_row( Module $module ) {

		$is_active = $module->is_active();

		$class = $is_active ? 'active' : 'inactive';

		$name  = 'mlp_state_' . $module->id();

		ob_start();
		?>
		<tr class="<?php echo esc_attr( $class ); ?>">
			<td class="check-column">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"
					id="id_<?php echo esc_attr( $name ); ?>"<?php checked( $is_active ); ?>>
			</td>
			<td>
				<label for="id_<?php echo esc_attr( $name ); ?>" class="mlp-block-label">
					<strong><?php echo esc_html( $module->name() ); ?></strong><br>
					<?php echo esc_html( $module->description() ); ?>
				</label>
			</td>
		</tr>

		<?php
		return ob_get_clean();
	}
}
