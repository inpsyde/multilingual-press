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

		$current = $this->data->get_current_option_value();
		$label   = __( 'Enable automatic redirection', 'multilingual-press' );
		$name    = $this->data->get_checkbox_name();
		$id      = "{$name}_id";
		?>
		<tr class="form-field">
			<th scope="row"><?php esc_html_e( 'Redirection', 'multilingual-press' ); ?></th>
			<td>
				<?php wp_nonce_field( $this->nonce->get_action(), $this->nonce->get_name() ); ?>
				<label for="<?php echo esc_attr( $id ); ?>">
					<input type="checkbox"
						<?php checked( 1, $current ); ?>
						id="<?php echo esc_attr( $id ); ?>"
						value="1"
						name="<?php echo esc_attr( $name ); ?>"
					>
					<?php echo esc_attr( $label ); ?>
				</label>
			</td>
		</tr>
	<?php
	}
}
