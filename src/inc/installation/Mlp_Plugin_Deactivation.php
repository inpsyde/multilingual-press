<?php
/**
 * ${CARET}
 *
 * @version 2014.08.29
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */


class Mlp_Plugin_Deactivation {

	/**
	 * @var Array
	 */
	private $errors;

	/**
	 * @var string
	 */
	private $plugin_name;

	/**
	 * @var string
	 */
	private $plugin_base_name;

	/**
	 * @param Array  $errors
	 * @param string $plugin_name
	 * @param string $plugin_base_name
	 */
	public function __construct(
		Array $errors,
		      $plugin_name,
		      $plugin_base_name
	) {

		$this->errors           = $errors;
		$this->plugin_name      = $plugin_name;
		$this->plugin_base_name = $plugin_base_name;
	}

	/**
	 * Trigger error message output and deactivate the plugin.
	 *
	 * @return bool Whether the plugin was deactivated or not.
	 */
	public function deactivate() {

		$this->print_errors();

		// Suppress "Plugin activated" notice.
		unset( $_GET[ 'activate' ] );

		deactivate_plugins( $this->plugin_base_name );

		return TRUE;
	}

	/**
	 * @return void
	 */
	private function print_errors() {

		?>
		<div class="error">
			<p>
				<strong>
					<?php
					printf(
						__( 'The plugin %s has been deactivated.', 'multilingual-press' ),
						esc_html( $this->plugin_name )
					);
					?>
				</strong>
			</p>
			<?php foreach ( $this->errors as $error ) : ?>
				<p>
					<?php echo $error; ?>
				</p>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
