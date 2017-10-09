<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Quicklinks;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Setting\SettingsBoxViewModel;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Quicklinks settings box.
 *
 * @package Inpsyde\MultilingualPress\Module\Quicklinks
 * @since   3.0.0
 */
final class QuicklinksSettingsBox implements SettingsBoxViewModel {

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var SettingsRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SettingsRepository $repository Settings repository object.
	 * @param Nonce              $nonce      Nonce object.
	 */
	public function __construct( SettingsRepository $repository, Nonce $nonce ) {

		$this->repository = $repository;

		$this->nonce = $nonce;
	}

	/**
	 * Returns the description.
	 *
	 * @since 3.0.0
	 *
	 * @return string The description.
	 */
	public function description(): string {

		return '';
	}

	/**
	 * Returns the ID of the container element.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID of the container element.
	 */
	public function id(): string {

		return 'mlp-quicklinks-settings';
	}

	/**
	 * Returns the ID of the form element to be used by the label in order to make it accessible for screen readers.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID of the primary form element.
	 */
	public function label_id(): string {

		return '';
	}

	/**
	 * Returns the markup for the settings box.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the settings box.
	 */
	public function markup(): string {

		$available_positions = $this->repository->get_available_positions();

		$markup = nonce_field( $this->nonce ) . '<p id="mlp-quicklinks">';

		ob_start();

		array_walk( $available_positions, [ $this, 'render_position' ], $this->repository->get_current_position() );

		$markup .= ob_get_clean() . '</p>';

		return $markup;
	}

	/**
	 * Returns the title of the settings box.
	 *
	 * @since 3.0.0
	 *
	 * @return string The title of the settings box.
	 */
	public function title(): string {

		return __( 'Quicklinks', 'multilingualpress' );
	}

	/**
	 * Renders the according HTML for the given position.
	 *
	 * @param string $name    Position name.
	 * @param string $key     Position setting value.
	 * @param string $current Currently selected position.
	 *
	 * @return void
	 */
	private function render_position( string $name, string $key, string $current ) {

		?>
		<label for="mlp-<?php echo esc_attr( $key ); ?>-id"
			class="quicklink-position-label quicklink-position-<?php echo esc_attr( $key ); ?>">
			<input type="radio" name="<?php echo esc_attr( SettingsUpdater::SETTINGS_NAME ); ?>[position]"
				value="<?php echo esc_attr( $key ); ?>" id="mlp-<?php echo esc_attr( $key ); ?>-id"
				<?php checked( $current, $key ); ?>>
			<?php echo esc_html( $name ); ?>
		</label>
		<?php
	}
}
