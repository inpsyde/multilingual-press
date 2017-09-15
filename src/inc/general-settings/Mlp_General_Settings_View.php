<?php # -*- coding: utf-8 -*-
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
	 * @since   0.1
	 * @uses    get_site_option, _e, admin_url, wp_nonce_field,
	 *          do_action, submit_button
	 * @return  void
	 */
	public function modules_form() {

		$modules = $this->module_mapper->get_modules();

		$action = admin_url( 'admin-post.php?action=mlp_update_modules' );

		// Draw the form
		?>
		<form action="<?php echo esc_url( $action ); ?>" method="post" id="mlp_modules">
			<?php wp_nonce_field( $this->module_mapper->get_nonce_action() ); ?>
			<table class="mlp-admin-feature-table">
				<?php
				foreach ( $modules as $slug => $module ) {
					/**
					 * Filter the visibility of the module in the features table.
					 *
					 * @param bool $invisible Should the module be hidden?
					 */
					if ( apply_filters( "mlp_dont_show_module_$slug", false ) ) {
						continue;
					}

					$this->module_row( $slug, $module );
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
	 * @param  string $slug
	 * @param  array  $module
	 * @return void
	 */
	protected function module_row( $slug, $module ) {

		// backwards compatibility check
		if ( is_array( $module['state'] ) && isset( $module['state']['state'] ) ) {
			$module['state'] = $module['state']['state'];
		}

		$class = 'on' === $module['state'] ? 'active' : 'inactive';
		$name  = "mlp_state_$slug";
		$title = $this->get_module_title( $module );
		$desc  = $this->get_module_description( $module );

		?>
		<tr class="<?php echo esc_attr( $class ); ?>">
			<td class="check-column">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"
					id="id_<?php echo esc_attr( $name ); ?>"<?php checked( 'on', $module['state'] ); ?>>
			</td>
			<td>
				<label for="id_<?php echo esc_attr( $name ); ?>" class="mlp-block-label">
					<strong><?php echo esc_html( $title ); ?></strong><br>
					<?php echo esc_html( $desc ); ?>
				</label>
				<?php
				if ( isset( $module['callback'] ) ) {
					call_user_func( $module['callback'] );
				}
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Get module title.
	 *
	 * @param $module
	 * @return string
	 */
	private function get_module_title( array $module ) {

		if ( isset( $module['display_name_callback'] ) ) {
			return call_user_func( $module['display_name_callback'] );
		}

		return $module['display_name'];
	}

	/**
	 * Get module description.
	 *
	 * @param array $module
	 * @return string
	 */
	private function get_module_description( array $module ) {

		if ( isset( $module['description_callback'] ) ) {
			return call_user_func( $module['description_callback'] );
		}

		return $module['description'];
	}
}
