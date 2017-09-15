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
	 * Constructor. Set up the properties.
	 *
	 * @param Mlp_Extra_General_Settings_Box_Data_Interface $data Settings box data.
	 */
	public function __construct( Mlp_Extra_General_Settings_Box_Data_Interface $data ) {

		$this->data = $data;
	}

	/**
	 * Print complete box content.
	 *
	 * @return void
	 */
	public function print_box() {

		$id = $this->data->get_box_id();

		$title = $this->data->get_title();

		$content = '';
		$content .= $this->get_main_description();
		$content .= $this->data->update( 'general.settings.extra.box' );

		$tags          = wp_kses_allowed_html( 'post' );
		$tags['input'] = array(
			'checked' => true,
			'id'      => true,
			'name'    => true,
			'type'    => true,
			'value'   => true,
		);
		?>
		<div class="mlp-extra-settings-box" id="<?php echo esc_attr( $id ); ?>">
			<?php if ( $title ) : ?>
				<h4><?php echo esc_html( $title ); ?></h4>
			<?php endif; ?>
			<?php echo wp_kses( $content, $tags ); ?>
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

		if ( ! $desc ) {
			return '';
		}

		$label_id = $this->data->get_main_label_id();

		if ( ! empty( $label_id ) ) {
			$desc = "<label for='$label_id' class='mlp-block-label'>$desc</label>";
		}

		return "<p>$desc</p>";
	}

}
