<?php
/**
 *
 * @author  toscho
 * @version 2014.03.03
 * @license MIT
 */

class Mlp_Cpt_Translator_Extra_General_Settings_Box_Data
	implements Mlp_Extra_General_Settings_Box_Data_Interface {

	/**
	 * @var Mlp_Updatable
	 */
	private $update;

	/**
	 * @var array
	 */
	private $post_types = array();

	/**
	 * Prefix for 'name' attribute in form fields.
	 *
	 * @type string
	 */
	private $form_name = 'mlp_cpts';

	/**
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce_validator;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Updatable                     $update
	 * @param Inpsyde_Nonce_Validator_Interface $nonce_validator
	 */
	public function __construct( Mlp_Updatable $update, Inpsyde_Nonce_Validator_Interface $nonce_validator ) {
		$this->update          = $update;
		$this->nonce_validator = $nonce_validator;
	}

	/**
	 * Get box title.
	 * @return string
	 */
	public function get_title() {
		return __(
			'Custom Post Type Translator Settings',
			'multilingual-press'
		);
	}

	/**
	 * Get the box description.
	 *
	 * @return string
	 */
	public function get_main_description() {
		return __(
			'In some cases the correct pretty permalinks are not available across multiple sites. Test it, and activate dynamic permalinks for those post types to avoid 404 errors. This will not change the permalink settings, just the URLs in MultilingualPress.',
			'multilingual-press'
		);
	}

	/**
	 * The ID used in the main form element.
	 *
	 * Used to wrap the description in a label element, so it is accessible for
	 * screen reader users.
	 *
	 * @return string
	 */
	public function get_main_label_id() {

		$post_types = $this->get_custom_post_types();

		if ( empty( $post_types ) ) {
			return '';
		}

		return 'mlp_cpt_' . key( $post_types );
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|void Either a value, or void for actions.
	 */
	public function update( $name ) {

		if ( 'general.settings.extra.box' === $name ) {
			return $this->get_box_content();
		}

		return '';
	}

	/**
	 * Value for ID attribute for the box.
	 *
	 * @return string
	 */
	public function get_box_id() {

		return 'mlp-cpt-settings';
	}

	/**
	 * Create the content for the extra box, a table with checkboxes.
	 *
	 * @return string
	 */
	private function get_box_content() {

		$post_types = $this->get_custom_post_types();
		$options    = (array) get_site_option( 'inpsyde_multilingual_cpt' );
		$s_label    = esc_html__( 'Use dynamic permalinks', 'multilingual-press' );

		if ( empty( $post_types ) ) {
			return '';
		}

		$out = wp_nonce_field(
			$this->nonce_validator->get_action(),
			$this->nonce_validator->get_name(),
			true,
			false
		);
		$out .= '<table><tbody>';

		foreach ( $post_types as $cpt => $cpt_params ) {
			$out .= $this->get_row( $cpt, $cpt_params, $options, $s_label );
		}

		return "$out</tbody></table>";
	}

	/**
	 * Create the table rows.
	 *
	 * @param string   $cpt
	 * @param stdClass $cpt_params
	 * @param array    $options
	 * @param string   $s_label
	 * @return string
	 */
	private function get_row( $cpt, $cpt_params, array $options, $s_label ) {

		$id = 'mlp_cpt_' . $cpt;

		if ( empty( $options['post_types'][ $cpt ] ) ) {
			$active = 0;
		} else {
			$active = (int) $options['post_types'][ $cpt ];
		}

		$check_use = $this->get_checkbox(
			$this->form_name . '[' . $cpt . ']',
			$id,
			$active
		);
		$check_dyn_links = $this->get_checkbox(
			$this->form_name . '[' . $cpt . '|links]',
			"{$id}|links",
			2 === $active
		);
		$name = esc_html( $cpt_params->labels->name );

		return "<tr>
			<td>
				<label for='$id' class='mlp-block-label'>
					$check_use
					$name
				</label>
			</td>
			<td>
				<label for='$id|links' class='mlp-block-label'>
					$check_dyn_links
					$s_label
				</label>
			</td>
		</tr>";
	}

	/**
	 * Checkbox view
	 *
	 * @param  string $name
	 * @param  string $id
	 * @param  bool   $checked
	 * @return string
	 */
	private function get_checkbox( $name, $id, $checked ) {

		return sprintf(
			'<input type="checkbox" value="1" name="%1$s" id="%2$s"%3$s> ',
			esc_attr( $name ),
			esc_attr( $id ),
			checked( (bool) $checked, true, false )
		);
	}

	/**
	 * Get allowed post types from controller.
	 *
	 * Not sure if this is a good solution.
	 *
	 * @return array
	 */
	private function get_custom_post_types() {

		if ( empty( $this->post_types ) ) {
			$this->post_types = $this->update->update( 'custom.post-type.list' );
		}

		return $this->post_types;
	}

}
