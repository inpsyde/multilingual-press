<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Quicklinks;

use Inpsyde\MultilingualPress\API\Translations;
use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Common\Type\Translation;
use Inpsyde\MultilingualPress\Common\Request;

/**
 * Quicklinks front-end view.
 *
 * @package Inpsyde\MultilingualPress\Module\Quicklinks
 * @since   3.0.0
 */
class Quicklinks {

	/**
	 * Name attribute value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME = 'mlp_quicklinks_url';

	/**
	 * Quicklinks type
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_LINKS = 'links';

	/**
	 * Quicklinks type
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_SELECT = 'select';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var SettingsRepository
	 */
	private $repository;

	/**
	 * @var Translations
	 */
	private $translations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Translations       $translations  Translations API object.
	 * @param SettingsRepository $repository    Settings repository object.
	 * @param AssetManager       $asset_manager Asset manager object.
	 */
	public function __construct(
		Translations $translations,
		SettingsRepository $repository,
		AssetManager $asset_manager
	) {

		$this->translations = $translations;

		$this->repository = $repository;

		$this->asset_manager = $asset_manager;

		$this->description = _x( 'Read in:', 'Quicklinks description', 'multilingual-press' );
	}

	/**
	 * Adds the quicklink UI to the post content.
	 *
	 * @since   3.0.0
	 * @wp-hook the_content
	 *
	 * @param string $content Current post content.
	 *
	 * @return string Post content.
	 */
	public function add_to_content( string $content ): string {

		$translations = $this->get_translations( get_current_blog_id() );
		if ( ! $translations ) {
			return $content;
		}

		$this->asset_manager->enqueue_script( 'multilingualpress' );
		$this->asset_manager->enqueue_style( 'multilingualpress' );

		$languages = array_reduce( $translations, function ( $languages, Translation $translation ) {

			return array_merge( $languages, [ $translation->remote_url() => $translation->language() ] );
		}, [] );

		$current_position = $this->repository->get_current_position();

		$quicklinks = $this->quicklinks( $languages, $current_position );

		/**
		 * Filters the quicklinks positions that require the HTML to be prepended to the post content.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $positions Position values for the quicklinks HTML to be prepended.
		 */
		$positions_to_prepend = apply_filters( 'multilingualpress.quicklinks_positions_to_prepend', [
			SettingsRepository::POSITION_TOP_LEFT,
			SettingsRepository::POSITION_TOP_RIGHT,
		] );

		if ( in_array( $current_position, $positions_to_prepend, true ) ) {
			return $quicklinks . $content;
		}

		return $content . $quicklinks;
	}

	/**
	 * Returns the translations.
	 *
	 * @param mixed $skip Optional. One or more keys to skip. Defaults to empty string.
	 *
	 * @return Translation[] An array of translation objects
	 */
	private function get_translations( $skip = '' ): array {

		if ( is_singular() ) {
			return array_diff_key(
				$this->translations->get_translations( [
					'type' => Request::TYPE_SINGULAR,
				] ),
				array_flip( (array) $skip )
			);
		}

		return [];
	}

	/**
	 * Returns the quicklinks HTML for the given languages.
	 *
	 * @param Language[] $languages        Language objects.
	 * @param string     $current_position Current quicklinks position.
	 *
	 * @return string Quicklinks HTML.
	 */
	private function quicklinks( array $languages, string $current_position ): string {

		/**
		 * Filters the type of the quicklinks output (i.e., a single select element, or individual links).
		 *
		 * @since 3.0.0
		 *
		 * @param string     $type             The type of the quicklinks output.
		 * @param Language[] $languages        Language objects.
		 * @param string     $current_position Current quicklinks position.
		 */
		$type = apply_filters(
			'multilingualpress.quicklinks_type',
			3 < count( $languages ) ? static::TYPE_LINKS : static::TYPE_SELECT,
			$languages,
			$current_position
		);

		$quicklinks = '<div class="mlp-quicklinks mlp-quicklinks-position-' . esc_attr( $current_position ) . '">';

		ob_start();

		if ( static::TYPE_LINKS === $type ) {
			$this->render_links( $languages );
		} else {
			$this->render_select( $languages );
		}

		/**
		 * Filters the quicklinks HTML.
		 *
		 * @since 3.0.0
		 *
		 * @param string     $quicklinks       The quicklinks HTML.
		 * @param Language[] $languages        Language objects.
		 * @param string     $current_position Current quicklinks position.
		 * @param string     $type             The type of the quicklinks output.
		 */
		$quicklinks .= apply_filters(
			'multilingualpress.quicklinks_html',
			ob_get_clean() . '</div>',
			$languages,
			$current_position,
			$type
		);

		return $quicklinks;
	}

	/**
	 * Renders the links HTML for the given languages.
	 *
	 * @param Language[] $languages Language objects.
	 *
	 * @return void
	 */
	private function render_links( array $languages ) {

		echo esc_html( $this->description );

		array_walk( $languages, function ( Language $language, $url ) {

			?>
			<a href="<?php echo esc_url( $url ); ?>" rel="alternate"
				hreflang="<?php echo esc_attr( $language->name( 'http' ) ); ?>">
				<?php echo esc_html( $language->name( 'native' ) ); ?>
			</a><br>
			<?php
		} );
	}

	/**
	 * Renders the select HTML for the given languages.
	 *
	 * @param Language[] $languages Language objects.
	 *
	 * @return void
	 */
	private function render_select( array $languages ) {

		$id = 'mlp-quicklinks-select';
		?>
		<form action="<?php echo esc_attr( home_url() ); ?>" method="post">
			<label for="<?php echo esc_attr( $id ); ?>">
				<?php echo esc_html( $this->description ); ?>
				<select name="mlp_quicklink_select" id="<?php echo esc_attr( $id ); ?>" autocomplete="off">
					<?php
					array_walk( $languages, function ( Language $language, $url ) {

						?>
						<option value="<?php echo esc_url( $url ); ?>">
							<?php echo esc_html( $language->name( 'native' ) ); ?>
						</option>
						<?php
					} );
					?>
				</select>
			</label>
			<button type="submit">
				<?php echo esc_html_x( 'Go', 'Quicklinks submit button', 'multilingual-press' ); ?>
			</button>
		</form>
		<?php
	}
}
