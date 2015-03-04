<?php

/**
 * Mlp_Quicklink_Positions_Data
 *
 * Provide data for the configuration in wp-admin/network/settings.php?page=mlp
 *
 * @version 2014.04.03
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Quicklink_Positions_Data
	implements Mlp_Extra_General_Settings_Box_Data_Interface {

	/**
	 * Prefix for 'name' attribute in form fields.
	 *
	 * @type string
	 */
	private $form_name = 'mlp_quicklink_position';

	/**
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce_validator;

	/**
	 * Constructor.
	 *
	 * @param Inpsyde_Nonce_Validator_Interface $nonce_validator
	 */
	public function __construct(
		Inpsyde_Nonce_Validator_Interface $nonce_validator
	) {
		$this->nonce_validator = $nonce_validator;
	}

	/**
	 * Get box title.
	 *
	 * Will be wrapped in h4 tags by the view if it is not empty.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Quicklink position', 'multilingualpress' );
	}

	/**
	 * Get the box description.
	 *
	 * Will be enclosed in p tags by the view, so make sure the markup
	 * is valid afterwards.
	 *
	 * @return string
	 */
	public function get_main_description() {
		return '';
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
		return '';
	}

	/**
	 * Value for ID attribute for the box.
	 *
	 * @return string
	 */
	public function get_box_id() {
		return $this->form_name . '_setting';
	}

	/**
	 * Respond to request for more content from the view.
	 *
	 * This is not ideal, I know.
	 *
	 * @param  string $name
	 * @return mixed  Either void for actions or a value.
	 */
	public function update( $name ) {

		if ( 'general.settings.extra.box' === $name )
			return $this->get_box_content();

		return '';
	}

	/**
	 * Create the content for the extra box, four illustrated checkboxes.
	 *
	 * @return string
	 */
	private function get_box_content() {

		$positions = $this->get_position_names();
		$current   = $this->get_current_position( $positions );
		$out       = '<p>';

		foreach ( $positions as $key => $label ) {
			$checked = checked( $current, $key, FALSE );
			$out .= sprintf(
				' <label for="mlp_%1$s_id" class="quicklink-position-label quicklink-position-%1$s">
					<input type="radio" name="quicklink-position" value="%1$s" id="mlp_%1$s_id" %2$s>
					%3$s
				</label>',
				$key,
				$checked,
				$label
			);
		}

		return $out . '<br class="clear"></p>';
	}

	/**
	 * Get the currently selected position.
	 *
	 * Default is bottom right.
	 *
	 * @param  array $positions
	 * @return string
	 */
	private function get_current_position( Array $positions ) {

		$options = get_site_option( 'inpsyde_multilingual_quicklink_options' );

		if ( ! empty ( $options[ 'mlp_quicklink_position' ] )
			and in_array( $options[ 'mlp_quicklink_position' ], array_keys( $positions ) )
		)
			return $options[ 'mlp_quicklink_position' ];

		end( $positions );

		return key( $positions );
	}

	/**
	 * Get keys and labels for the positions.
	 *
	 * @return array
	 */
	private function get_position_names() {

		return array (
			'tl' => esc_attr__( 'Top left', 'multilingualpress' ),
			'tr' => esc_attr__( 'Top right', 'multilingualpress' ),
			'bl' => esc_attr__( 'Bottom left', 'multilingualpress' ),
			'br' => esc_attr__( 'Bottom right', 'multilingualpress' ),
		);
	}
}