<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use Inpsyde\MultilingualPress\Widget\Dashboard\View;

/**
 * Untranslated posts widget configuration view.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
final class WidgetConfigurationView implements View {

	/**
	 * @var WidgetConfigurator
	 */
	private $configurator;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param WidgetConfigurator $configurator Widget configuator object.
	 */
	public function __construct( WidgetConfigurator $configurator ) {

		$this->configurator = $configurator;
	}

	/**
	 * Renders the widget's configuration view.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $object   Queried object, or other stuff.
	 * @param array $instance Widget configuration settings.
	 *
	 * @return void
	 */
	public function render( $object, array $instance ) {

		$this->configurator = $this->configurator->with_widget_id( (string) $instance['id'] );

		$this->configurator->update();

		$id = 'mlp-untranslated-posts-display-remote-sites';

		$name = WidgetConfigurator::NAME_BASE . '[' . WidgetConfigurator::NAME_DISPLAY_REMOTE_SITES . ']';

		$value = $this->configurator->is_displaying_remote_sites();
		?>
		<input type="hidden" name="<?php echo esc_attr( WidgetConfigurator::NAME_BASE ); ?>" value="1">
		<p>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>"<?php checked( $value ); ?>>
				<?php esc_html_e( 'Display remote sites', 'multilingualpress' ); ?>
			</label>
		</p>
		<?php
	}
}
