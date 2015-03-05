<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Extra_General_Settings_Box
 *
 * Additional box to show more settings for a feature.
 *
 * @version 2014.03.03
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Extra_General_Settings_Box {

	/**
	 * Box data
	 *
	 * @type Mlp_Extra_General_Settings_Box_Data_Interface
	 */
	private $data;

	/**
	 * Constructor.
	 */
	public function __construct(
		Mlp_Extra_General_Settings_Box_Data_Interface $data
	) {
		$this->data = $data;
	}

	/**
	 * Print complete box content.
	 *
	 * @return void
	 */
	public function print_box() {
		?>
		<div class="mlp-extra-settings-box" id="<?php print $this->data->get_box_id(); ?>">
			<?php

			$title = $this->data->get_title();

			if ( ! empty ( $title ) )
				print "<h4>$title</h4>";

			print $this->get_main_description();
			print $this->data->update( 'general.settings.extra.box' );
			?>
		</div>
		<?php
	}

	/**
	 * Get the box description.
	 *
	 * @return string
	 */
	private function get_main_description() {

		$desc = $this->data->get_main_description();

		if ( ! $desc )
			return '';

		$label_id = $this->data->get_main_label_id();

		if ( ! empty ( $label_id ) )
			$desc = "<label for='$label_id' class='mlp-block-label'>$desc</label>";

		return "<p>$desc</p>";
	}
}