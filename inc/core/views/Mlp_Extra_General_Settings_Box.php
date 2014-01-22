<?php # -*- coding: utf-8 -*-
class Mlp_Extra_General_Settings_Box {

	/**
	 * Box data
	 *
	 * @type Inpsyde_Property_List_Interface
	 */
	protected $data;

	/**
	 * Constructor.
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {
		$this->data = $data;
	}

	public function print_box() {

		?>
		<div class="mlp-extra-settings-box" id="<?php print $this->data->box_id; ?>">
			<h4><?php print $this->data->title; ?></h4>
			<?php
			print $this->get_main_description();
			print $this->get_box_content();
			?>
		</div>
		<?php
	}

	protected function get_main_description() {

		$desc = $this->data->main_description;

		if ( ! $desc )
			return '';

		if ( $this->data->main_desc_label )
			$desc = "<label for='{$this->data->main_desc_label}' class='mlp-block-label'>$desc</label>";

		return "<p>$desc</p>";
	}

	protected function get_box_content() {

		$out = '<table><tbody>';

		foreach ( $this->data->post_types as $cpt => $cpt_params )
			$out .= $this->get_row( $cpt, $cpt_params );

		return "$out</tbody></table>";
	}

	protected function get_row( $cpt, $cpt_params ) {

		$id     = 'mlp_cpt_' . $cpt;

		if ( empty ( $this->data->options[ 'post_types' ][ $cpt ] ) )
			$active = 0;
		else
			$active = (int) $this->data->options[ 'post_types' ][ $cpt ];

		return '<tr><td><label for="' . $id . '" class="mlp-block-label">'
			. $this->get_checkbox( $this->data->form_name . '[' . $cpt . ']', $id, $active )
			. esc_html( $cpt_params->labels->name ) . '</label>'
			. '</td><td><label for="' . "{$id}|links" . '" class="mlp-block-label">'
			. $this->get_checkbox( $this->data->form_name . '[' . $cpt . '|links]', "{$id}|links", 2 === $active )
			. $this->data->s_label . '</label></td></tr>';
	}

	/**
	 * Checkbox view
	 *
	 * @param  string $name
	 * @param  string $id
	 * @param  bool   $checked
	 * @return string
	 */
	protected function get_checkbox( $name, $id, $checked ) {

		return sprintf(
			'<input type="checkbox" value="1" name="%1$s" id="%2$s" %3$s> ',
			$name,
			$id,
			$checked ? ' checked="checked"' : ''
		);
	}
}