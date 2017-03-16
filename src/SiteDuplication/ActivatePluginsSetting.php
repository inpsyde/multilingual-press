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
	 * Returns the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string The markup for the site setting.
	 */
	public function markup( int $site_id ): string {

		return sprintf(
			'<label for="%2$s"><input type="checkbox" value="1" id="%2$s" name="blog[%3$s]" checked="checked">%1$s</label>',
			esc_html__( 'Activate all plugins that are active on the source site', 'multilingual-press' ),
			esc_attr( $this->id ),
			esc_attr( SiteDuplicator::NAME_ACTIVATE_PLUGINS )
		);
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
			esc_html__( 'Plugins', 'multilingual-press' ),
			esc_attr( $this->id )
		);
	}
}
