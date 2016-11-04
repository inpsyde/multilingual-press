<?php

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

/**
 * Show the option field in the site settings tab.
 *
 * @version    2014.04.26
 * @author     Inpsyde GmbH, toscho
 * @license    GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect_Site_Settings_Form {

	/**
	 * @var Mlp_Redirect_Settings_Data_Interface
	 */
	private $data;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @param Nonce                                $nonce Nonce object.
	 * @param Mlp_Redirect_Settings_Data_Interface $data
	 */
	public function __construct( Nonce $nonce, Mlp_Redirect_Settings_Data_Interface $data ) {

		$this->nonce = $nonce;

		$this->data = $data;
	}

	public function render() {

		$name = $this->data->get_checkbox_name();

		$id = "{$name}_id";
		?>
		<tr class="form-field">
			<th scope="row"><?php esc_html_e( 'Redirection', 'multilingual-press' ); ?></th>
			<td>
				<label for="<?php echo esc_attr( $id ); ?>">
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"
						id="<?php echo esc_attr( $id ); ?>"
						<?php checked( 1, $this->data->get_current_option_value() ); ?>
					>
					<?php esc_html_e( 'Enable automatic redirection', 'multilingual-press' ); ?>
					<?php echo \Inpsyde\MultilingualPress\nonce_field( $this->nonce ); ?>
				</label>
			</td>
		</tr>
		<?php
	}
}
