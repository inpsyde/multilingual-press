<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

/**
 * Site duplication "Plugins" setting.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
final class ActivatePluginsSetting implements SiteSettingViewModel {

	/**
	 * @var string
	 */
	private $id = 'mlp-activate-plugins';

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
		<label for="<?php echo esc_attr( $this->id ); ?>">
			<input type="checkbox" value="1" id="<?php echo esc_attr( $this->id ); ?>"
				name="<?php echo esc_attr( SiteDuplicator::NAME_ACTIVATE_PLUGINS ); ?>" checked="checked">
			<?php esc_html_e( 'Activate all plugins that are active on the source site', 'multilingualpress' ); ?>
		</label>
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
			esc_html__( 'Plugins', 'multilingualpress' ),
			esc_attr( $this->id )
		);
	}
}
