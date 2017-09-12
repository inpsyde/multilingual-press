<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Setting;

/**
 * Settings box view to show additional information (e.g., for a module).
 *
 * @package Inpsyde\MultilingualPress\Common\Setting
 * @since   3.0.0
 */
class SettingsBoxView {

	/**
	 * @var SettingsBoxViewModel
	 */
	private $model;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SettingsBoxViewModel $model Settings box view model object.
	 */
	public function __construct( SettingsBoxViewModel $model ) {

		$this->model = $model;
	}

	/**
	 * Renders the complete settings box content.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render() {

		static $tags;
		if ( ! $tags ) {
			$tags = wp_kses_allowed_html( 'post' );

			$tags['input'] = [
				'checked' => true,
				'id'      => true,
				'name'    => true,
				'type'    => true,
				'value'   => true,
			];
		}
		?>
		<div class="mlp-extra-settings-box" id="<?php echo esc_attr( $this->model->id() ); ?>">
			<?php
			$this->render_title();
			$this->render_description();
			echo wp_kses( $this->model->markup(), $tags );
			?>
		</div>
		<?php
	}

	/**
	 * Renders the title, if not empty.
	 *
	 * @return void
	 */
	private function render_title() {

		$title = $this->model->title();
		if ( ! $title ) {
			return;
		}
		?>
		<h4><?php echo esc_html( $title ); ?></h4>
		<?php
	}

	/**
	 * Renders the description, if not empty.
	 *
	 * @return void
	 */
	private function render_description() {

		$description = $this->model->description();
		if ( ! $description ) {
			return;
		}

		$label_id = $this->model->label_id();
		if ( $label_id ) {
			$description = sprintf(
				'<label for="%2$s" class="mlp-block-label">%1$s</label>',
				$description,
				esc_attr( $label_id )
			);
		}

		$tags = [
			'label' => [
				'class' => true,
				'for'   => true,
			],
		];

		echo wp_kses( wpautop( $description ), $tags );
	}
}
