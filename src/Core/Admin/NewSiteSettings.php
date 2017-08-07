<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingsSectionViewModel;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingView;

/**
 * New site settings section view model implementation.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class NewSiteSettings implements SiteSettingsSectionViewModel {

	/**
	 * Section ID.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ID = 'mlp-new-site-settings';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var SiteSettingView
	 */
	private $view;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingView $view          Setting view object.
	 * @param AssetManager    $asset_manager Asset manager object.
	 */
	public function __construct( SiteSettingView $view, AssetManager $asset_manager ) {

		$this->view = $view;

		$this->asset_manager = $asset_manager;
	}

	/**
	 * Returns the ID of the site settings section.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID for the site settings section.
	 */
	public function id(): string {

		return static::ID;
	}

	/**
	 * Returns the markup for the site settings section.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the site setting markup was rendered successfully.
	 */
	public function render_view( int $site_id ): bool {

		$this->asset_manager->enqueue_script( 'multilingualpress-admin' );

		return $this->view->render( $site_id );
	}

	/**
	 * Returns the title of the site settings section.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the site settings section.
	 */
	public function title(): string {

		return sprintf(
			'<h2>%s</h2>',
			esc_html__( 'MultilingualPress', 'multilingualpress' )
		);
	}
}
