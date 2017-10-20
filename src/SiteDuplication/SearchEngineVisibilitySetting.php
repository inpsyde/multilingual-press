<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

/**
 * Site duplication "Search Engine Visibility" setting.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
final class SearchEngineVisibilitySetting implements SiteSettingViewModel {

	/**
	 * @var string
	 */
	private $id = 'mlp-search-engine-visibility';

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

		/**
		 * Filters the default search engine visibility value when adding a new site.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $visible Whether or not the new site should be visible by default.
		 */
		$visible = (bool) apply_filters( 'multilingualpress.new_site_search_engine_visibility', false );
		?>
		<label for="<?php echo esc_attr( $this->id ); ?>">
			<input type="checkbox" value="1" id="<?php echo esc_attr( $this->id ); ?>"
				name="<?php echo esc_attr( SiteDuplicator::NAME_SEARCH_ENGINE_VISIBILITY ); ?>"
				<?php checked( ! $visible ); ?>>
			<?php esc_html_e( 'Discourage search engines from indexing this site', 'multilingualpress' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'It is up to search engines to honor this request.', 'multilingualpress' ); ?>
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
			esc_html__( 'Search Engine Visibility', 'multilingualpress' ),
			esc_attr( $this->id )
		);
	}
}
