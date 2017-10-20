<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

/**
 * MultilingualPress "Alternative language title" site setting.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class AlternativeLanguageTitleSiteSetting implements SiteSettingViewModel {

	/**
	 * @var string
	 */
	private $id = 'mlp-alternative-language-title';

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
	 */
	public function __construct( SiteSettingsRepository $repository ) {

		$this->repository = $repository;
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
		<input type="text" name="<?php echo esc_attr( SiteSettingsRepository::NAME_ALTERNATIVE_LANGUAGE_TITLE ); ?>"
			value="<?php echo esc_attr( $this->repository->get_alternative_language_title( $site_id ) ); ?>"
			class="regular-text" id="<?php echo esc_attr( $this->id ); ?>">
		<p class="description">
			<?php
			esc_html_e(
				'Enter a title here that you want to be displayed in the frontend instead of the default one (i.e. "My English Site")',
				'multilingualpress'
			);
			?>
		</p>
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
			esc_html__( 'Alternative language title', 'multilingualpress' ),
			esc_attr( $this->id )
		);
	}
}
