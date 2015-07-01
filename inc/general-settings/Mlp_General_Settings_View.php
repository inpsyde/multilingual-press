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
			<h2><?php print $GLOBALS[ 'title' ]; ?></h2>
			<?php
			$this->modules_form();
			print $this->get_marketpress_pointer();
			?>
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
		<form action="<?php echo admin_url( 'admin-post.php?action=mlp_update_modules' ); ?>" method="post" id="mlp_modules">
			<?php wp_nonce_field( $this->module_mapper->get_nonce_action() ); ?>

			<table class="mlp-admin-feature-table">
			<?php

			foreach ( $modules as $slug => $module ) {

				/**
				 * Filter the visibility of the module in the features table.
				 *
				 * @param bool $invisible Should the module be hidden?
				 *
				 * @return bool
				 */
				if ( apply_filters( "mlp_dont_show_module_$slug", FALSE ) ) {
					continue;
				}

				print $this->module_row( $slug, $module );

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
			submit_button( __( 'Save changes', 'multilingualpress' ) );
			?>
		</form>
		<?php
	}

	/**
	 * Create markup for activation rows.
	 *
	 * @param  string $slug
	 * @param  array  $module
	 * @return string
	 */
	protected function module_row( $slug, $module ) {

		// backwards compatibility check
		if ( is_array( $module[ 'state' ] ) && isset ( $module[ 'state' ][ 'state' ] ) )
			$module[ 'state' ] = $module[ 'state' ][ 'state' ];

		$class   = 'on' === $module[ 'state' ] ? 'active' : 'inactive';
		$name    = "mlp_state_$slug";
		$title   = $this->get_module_title( $module );
		$desc    = $this->get_module_description( $module );
		$extra   = empty ( $module[ 'callback' ] ) ? '' : call_user_func( $module[ 'callback' ] );
		$checked = checked( 'on', $module[ 'state' ], FALSE );

		return
<<<EOD
<tr class='$class'>
	<td class="check-column">
		<input type='checkbox' id='id_$name' value='1' name='$name' $checked />
	</td>
	<td>
		<label for='id_$name' class='mlp-block-label'>
			<strong>$title</strong><br />
			$desc
		</label>
		$extra
	</td>
</tr>
EOD;
	}

	/**
	 * Tell our users who built this. :)
	 *
	 * @return string
	 */
	private function get_marketpress_pointer() {

		$marketpress_url = __( 'http://marketpress.com/', 'multilingualpress' );
		$inpsyde_url     = __( 'http://inpsyde.com/',     'multilingualpress' );
		$message         = __(
			'This plugin has been developed by <a href="%1$s">MarketPress</a>, a project of <a href="%2$s">Inpsyde</a>.',
			'multilingualpress'
		);

		return '<p>' . sprintf( $message, $marketpress_url, $inpsyde_url ) . '</p>';
	}

	/**
	 * Get module title.
	 *
	 * @param $module
	 * @return string
	 */
	private function get_module_title( Array $module ) {

		if ( isset ( $module[ 'display_name_callback' ] ) )
			return call_user_func( $module[ 'display_name_callback' ] );

		return $module[ 'display_name' ];
	}

	/**
	 * Get module description.
	 *
	 * @param array $module
	 * @return string
	 */
	private function get_module_description( Array $module ) {

		if ( isset ( $module[ 'description_callback' ] ) )
			return call_user_func( $module[ 'description_callback' ] );

		return $module[ 'description' ];
	}

}
