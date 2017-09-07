<?php # -*- coding: utf-8 -*-

/**
 * Provides data for the configuration on the MultilingualPress network settings page.
 */
class Mlp_Quicklink_Positions_Data implements Mlp_Extra_General_Settings_Box_Data_Interface {

	/**
	 * Prefix for 'name' attribute in form fields.
	 *
	 * @var string
	 */
	private $form_name = 'mlp-quicklink-position';

	/**
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce_validator;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param Inpsyde_Nonce_Validator_Interface $nonce_validator Nonce validator object.
	 */
	public function __construct( Inpsyde_Nonce_Validator_Interface $nonce_validator ) {

		$this->nonce_validator = $nonce_validator;
	}

	/**
	 * Returns the box title.
	 *
	 * Will be wrapped in h4 tags by the view if it is not empty.
	 *
	 * @return string
	 */
	public function get_title() {

		return esc_html__( 'Quicklink position', 'multilingual-press' );
	}

	/**
	 * Returns the box description.
	 *
	 * Will be enclosed in p tags by the view, so make sure the markup is valid afterwards.
	 *
	 * @return string
	 */
	public function get_main_description() {

		return '';
	}

	/**
	 * Returns the ID used in the main form element.
	 *
	 * Used to wrap the description in a label element, so it is accessible for screen reader users.
	 *
	 * @return string
	 */
	public function get_main_label_id() {

		return '';
	}

	/**
	 * Returns the value for ID attribute for the box.
	 *
	 * @return string
	 */
	public function get_box_id() {

		return $this->form_name . '-setting';
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
	 * Creates the content for the extra box, four illustrated checkboxes.
	 *
	 * @return string
	 */
	private function get_box_content() {

		$positions = $this->get_position_names();

		$current = $this->get_current_position( $positions );

		$out = wp_nonce_field( $this->nonce_validator->get_action(), $this->nonce_validator->get_name(), true, false );
		$out .= '<p id="mlp-quicklink-positions">';

		foreach ( $positions as $key => $label ) {
			$checked = checked( $current, $key, false );

			$out .= sprintf(
				' <label for="mlp-%1$s-id" class="quicklink-position-label quicklink-position-%1$s">
					<input type="radio" name="quicklink-position" value="%1$s" id="mlp-%1$s-id" %2$s>
					%3$s
				</label>',
				$key,
				$checked,
				$label
			);
		}

		return $out . '</p>';
	}

	/**
	 * Returns the currently selected position.
	 *
	 * Default is bottom right.
	 *
	 * @param string[] $positions Positions.
	 *
	 * @return string
	 */
	private function get_current_position( array $positions ) {

		$positions = array_keys( $positions );

		$options = get_site_option( 'inpsyde_multilingual_quicklink_options' );

		if (
			! empty( $options['mlp_quicklink_position'] )
			&& in_array( $options['mlp_quicklink_position'], $positions, true )
		) {
			return $options['mlp_quicklink_position'];
		}

		return array_pop( $positions );
	}

	/**
	 * Returns the keys and labels for the positions.
	 *
	 * @return string[]
	 */
	private function get_position_names() {

		return array(
			'tl' => esc_attr__( 'Top left', 'multilingual-press' ),
			'tr' => esc_attr__( 'Top right', 'multilingual-press' ),
			'bl' => esc_attr__( 'Bottom left', 'multilingual-press' ),
			'br' => esc_attr__( 'Bottom right', 'multilingual-press' ),
		);
	}
}
