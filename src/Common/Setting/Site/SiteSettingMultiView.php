<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Setting\Site;

/**
 * Site setting view implementation for multiple single settings.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\Site
 * @since   3.0.0
 */
final class SiteSettingMultiView implements SiteSettingView {

	/**
	 * @var bool
	 */
	private $check_user;

	/**
	 * @var SiteSettingView[]
	 */
	private $views;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingView[] $views      Setting view objects.
	 * @param bool              $check_user Optional. Only render for users capable of editing? Defaults to true.
	 */
	public function __construct( array $views, bool $check_user = true ) {

		$this->views = array_filter( $views, function ( $view ) {

			return $view instanceof SiteSettingView;
		} );

		$this->check_user = (bool) $check_user;
	}

	/**
	 * Returns a new instance
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingViewModel[] $settings   Setting view model objects.
	 * @param bool                   $check_user Optional. Only render for users capable of editing? Defaults to true.
	 *
	 * @return SiteSettingMultiView
	 */
	public static function from_view_models( array $settings, $check_user = true ): SiteSettingMultiView {

		$settings = array_filter( $settings, function ( $setting ) {

			return $setting instanceof SiteSettingViewModel;
		} );

		$views = array_map( function ( SiteSettingViewModel $setting ) {

			return new SiteSettingSingleView( $setting, false );
		}, $settings );

		return new static( $views, $check_user );
	}

	/**
	 * Renders the site settings markup.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the site settings markup was rendered successfully.
	 */
	public function render( int $site_id ): bool {

		if ( $this->check_user && ! current_user_can( 'manage_sites' ) ) {
			return false;
		}

		if ( ! $this->views ) {
			return false;
		}

		array_walk( $this->views, function ( SiteSettingView $view ) use ( $site_id ) {

			$view->render( $site_id );
		} );

		return true;
	}
}
