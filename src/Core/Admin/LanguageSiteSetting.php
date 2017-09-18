<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;
use Inpsyde\MultilingualPress\Common\Type\Language;

/**
 * MultilingualPress "Language" site setting.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class LanguageSiteSetting implements SiteSettingViewModel {

	/**
	 * @var string
	 */
	private $id = 'mlp-site-language';

	/**
	 * @var Languages
	 */
	private $languages;

	/**
	 * @var SiteSettingsRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingsRepository $repository Site settings repository object.
	 * @param Languages              $languages  Languages API object.
	 */
	public function __construct( SiteSettingsRepository $repository, Languages $languages ) {

		$this->repository = $repository;

		$this->languages = $languages;
	}

	/**
	 * Renders the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	public function render( int $site_id ) {

		?>
		<select id="<?php echo esc_attr( $this->id ); ?>"
			name="<?php echo esc_attr( SiteSettingsRepository::NAME_LANGUAGE ); ?>" autocomplete="off">
			<?php $this->render_options( $site_id ); ?>
		</select>
		<?php
	}

	/**
	 * Returns the title of the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the site setting.
	 */
	public function title(): string {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Language', 'multilingualpress' ),
			esc_attr( $this->id )
		);
	}

	/**
	 * Renders the option tags.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function render_options( int $site_id ) {

		?>
		<option value="-1"><?php esc_html_e( 'Choose language', 'multilingualpress' ); ?></option>
		<?php
		$languages = $this->get_languages();
		if ( ! $languages ) {
			return;
		}

		$current_site_language = $this->repository->get_site_language( $site_id ) ?: 'en_US';

		foreach ( $languages as $language ) {
			$site_language = str_replace( '-', '_', $language['http_code'] );
			?>
			<option value="<?php echo esc_attr( $site_language ); ?>"
				<?php selected( $site_language, $current_site_language ); ?>>
				<?php echo esc_html( $this->get_language_name( $language ) ); ?>
			</option>
			<?php
		}
	}

	/**
	 * Returns the name of the given language.
	 *
	 * @param Language $language Language object.
	 *
	 * @return string The name of the given language.
	 */
	private function get_language_name( Language $language ): string {

		return implode( '/', array_filter( array_unique( [
			$language['english_name'] ?? '',
			$language['native_name'] ?? '',
		] ) ) );
	}

	/**
	 * Returns the languages.
	 *
	 * @return Language[] The array with objects of all languages according to the given arguments.
	 */
	private function get_languages(): array {

		$languages = $this->languages->get_languages( [
			'fields' => [
				'english_name',
				'http_code',
				'native_name',
			],
		] );
		if ( ! $languages ) {
			return [];
		}

		return array_filter( $languages, function ( Language $language ) {

			return
				isset( $language['http_code'] )
				&& ( isset( $language['english_name'] ) || isset( $language['native_name'] ) );
		} );
	}
}
