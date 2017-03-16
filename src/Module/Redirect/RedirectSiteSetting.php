<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

/**
 * Redirect site setting.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
class RedirectSiteSetting implements SiteSettingViewModel {

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var string
	 */
	private $option;

	/**
	 * @var SettingsRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string             $option     Option name.
	 * @param Nonce              $nonce      Nonce object.
	 * @param SettingsRepository $repository Settings repository object.
	 */
	public function __construct( string $option, Nonce $nonce, SettingsRepository $repository ) {

		$this->option = $option;

		$this->nonce = $nonce;

		$this->repository = $repository;
	}

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
			'<label for="%2$s"><input type="checkbox" name="%2$s" value="1" id="%2$s"%3$s>%1$s</label>%4$s',
			esc_html__( 'Enable automatic redirect', 'multilingual-press' ),
			esc_attr( $this->option ),
			checked( $this->repository->get_site_setting( $site_id ), true, false ),
			\Inpsyde\MultilingualPress\nonce_field( $this->nonce )
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
			esc_html__( 'Redirect', 'multilingual-press' ),
			esc_attr( $this->option )
		);
	}
}
