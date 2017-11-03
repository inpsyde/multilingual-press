<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Tab for all Edit Site pages.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
class EditSiteTab {

	/**
	 * @var SettingsPageTabData
	 */
	private $data;

	/**
	 * @var SettingsPage
	 */
	private $settings_page;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @sine 3.0.0
	 *
	 * @param SettingsPageTab $tab Tab object.
	 */
	public function __construct( SettingsPageTab $tab ) {

		$this->data = $tab->data();

		$this->settings_page = SettingsPage::with_parent(
			SettingsPage::ADMIN_NETWORK,
			SettingsPage::PARENT_SITES,
			$tab->title(),
			'',
			$tab->capability(),
			$tab->slug(),
			$tab->view()
		);
	}

	/**
	 * Registers both the tab and the settings page for the tab.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the tab was registered successfully.
	 */
	public function register(): bool {

		if ( did_action( 'adminmenu' ) ) {
			return false;
		}

		if ( ! $this->settings_page->register() ) {
			return false;
		}

		add_action( 'network_admin_menu', function () {

			remove_submenu_page( SettingsPage::PARENT_SITES, $this->settings_page->slug() );

			add_action( 'load-' . $this->settings_page->hook_name(), function () {

				// @codingStandardsIgnoreLine
				$GLOBALS['parent_file'] = SettingsPage::PARENT_SITES;

				// @codingStandardsIgnoreLine
				$GLOBALS['submenu_file'] = SettingsPage::PARENT_SITES;
			} );

			add_filter( 'network_edit_site_nav_links', function ( array $links = [] ) {

				$links[ $this->data->id() ] = [
					'label' => esc_html( $this->data->title() ),
					'url'   => add_query_arg( 'page', $this->data->slug(), SettingsPage::PARENT_SITES ),
					'cap'   => $this->data->capability(),
				];

				return $links;
			} );
		}, 20 );

		return true;
	}
}
