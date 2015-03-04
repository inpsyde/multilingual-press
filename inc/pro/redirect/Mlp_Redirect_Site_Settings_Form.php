<?php
/**
 * Show the option field in the site settings tab.
 *
 * @version 2014.04.26
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect_Site_Settings_Form {

	/**
	 * Nonce validator.
	 *
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce;

	/**
	 * @var Mlp_Redirect_Settings_Data_Interface
	 */
	private $data;

	/**
	 * @param Inpsyde_Nonce_Validator_Interface    $nonce
	 * @param Mlp_Redirect_Settings_Data_Interface $data
	 */
	public function __construct(
		Inpsyde_Nonce_Validator_Interface $nonce,
		Mlp_Redirect_Settings_Data_Interface $data
	) {
		$this->nonce = $nonce;
		$this->data  = $data;
	}

	public function render() {

		wp_nonce_field( $this->nonce->get_action(), $this->nonce->get_name() );

		$current = $this->data->get_current_option_value();
		$label   = esc_attr__( 'Enable automatic redirection', 'multilingualpress' );
		$name    = $this->data->get_checkbox_name();
		$id      = "{$name}_id";
		?>
		<tr>
			<td><?php esc_html_e( 'Redirection', 'multilingualpress' ); ?></td>
			<td>
				<label for="<?php print $id; ?>">
					<input type="checkbox" <?php
					checked( 1, $current );
					?> id="<?php
					print $id;
					?>" value="1" name="<?php
					print $name;
					?>" />
					<?php print $label; ?>
				</label>
			</td>
		</tr>
	<?php
	}
}