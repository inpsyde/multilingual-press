<?php # -*- coding: utf-8 -*-

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
	 * Returns the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string The markup for the site setting.
	 */
	public function markup( $site_id ) {

		return sprintf(
			'<input type="text" name="%3$s" value="%1$s" class="regular-text" id="%2$s"><p class="description">%4$s</p>',
			esc_attr( $this->repository->get_alternative_language_title( $site_id ) ),
			esc_attr( $this->id ),
			esc_attr( SiteSettingsRepository::NAME_ALTERNATIVE_LANGUAGE_TITLE ),
			esc_html__(
				'Enter a title here that you want to be displayed in the frontend instead of the default one (i.e. "My English Site")',
				'multilingual-press'
			)
		);
	}

	/**
	 * Returns the title of the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the site setting.
	 */
	public function title() {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Alternative language title', 'multilingual-press' ),
			esc_attr( $this->id )
		);
	}
}
