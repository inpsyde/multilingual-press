<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\Common\Admin\ActionLink;
use Inpsyde\MultilingualPress\Common\Admin\EditSiteTab;
use Inpsyde\MultilingualPress\Common\Admin\EditSiteTabData;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPage;
use Inpsyde\MultilingualPress\Common\Admin\SitesListTableColumn;
use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\ConditionalAwareRequest;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingMultiView;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingsSectionView;
use Inpsyde\MultilingualPress\Core\Admin\AlternativeLanguageTitleSiteSetting;
use Inpsyde\MultilingualPress\Core\Admin\FlagImageURLSiteSetting;
use Inpsyde\MultilingualPress\Core\Admin\LanguageSiteSetting;
use Inpsyde\MultilingualPress\Core\Admin\NewSiteSettings;
use Inpsyde\MultilingualPress\Core\Admin\PluginSettingsPageView;
use Inpsyde\MultilingualPress\Core\Admin\PluginSettingsUpdater;
use Inpsyde\MultilingualPress\Core\Admin\RelationshipsSiteSetting;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettings;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsTabView;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsUpdater;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsUpdateRequestHandler;
use Inpsyde\MultilingualPress\Core\Admin\TypeSafeSiteSettingsRepository;
use Inpsyde\MultilingualPress\Module;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;

use function Inpsyde\MultilingualPress\get_available_language_names;
use function Inpsyde\MultilingualPress\get_language_field_by_http_code;
use function Inpsyde\MultilingualPress\get_site_language;

/**
 * Service provider for all Core objects.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
final class CoreServiceProvider implements BootstrappableServiceProvider {

	/**
	 * Registers the provided services on the given container.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function register( Container $container ) {

		$container['multilingualpress.alternative_language_title_site_setting'] = function ( Container $container ) {

			return new AlternativeLanguageTitleSiteSetting(
				$container['multilingualpress.site_settings_repository']
			);
		};

		$container['multilingualpress.base_path_adapter'] = function () {

			return new CachingBasePathAdapter();
		};

		$container['multilingualpress.flag_image_url_site_setting'] = function ( Container $container ) {

			return new FlagImageURLSiteSetting(
				$container['multilingualpress.site_settings_repository']
			);
		};

		$container->share( 'multilingualpress.internal_locations', function () {

			return new InternalLocations();
		} );

		$container['multilingualpress.language_site_setting'] = function ( Container $container ) {

			return new LanguageSiteSetting(
				$container['multilingualpress.site_settings_repository'],
				$container['multilingualpress.languages']
			);
		};

		// TODO: Make a regular not shared service as soon as everything else has been adapted.
		$container->share( 'multilingualpress.module_manager', function () {

			// TODO: Maybe store the option name somewhere? But then again, who else really needs to know it?
			// TODO: Migration: The old option name was "state_modules", and it stored "on" and "off" values, no bools.
			return new Module\NetworkOptionModuleManager( 'multilingualpress_modules' );
		} );

		$container['multilingualpress.new_site_settings'] = function ( Container $container ) {

			return new NewSiteSettings(
				$container['multilingualpress.site_settings_view']
			);
		};

		$container['multilingualpress.plugin_settings_page'] = function ( Container $container ) {

			return SettingsPage::with_parent(
				SettingsPage::ADMIN_NETWORK,
				SettingsPage::PARENT_NETWORK_SETTINGS,
				__( 'MultilingualPress', 'multilingual-press' ),
				__( 'MultilingualPress', 'multilingual-press' ),
				'manage_network_options',
				'multilingualpress',
				$container['multilingualpress.plugin_settings_page_view']
			);
		};

		$container['multilingualpress.plugin_settings_page_view'] = function ( Container $container ) {

			return new PluginSettingsPageView(
				$container['multilingualpress.module_manager'],
				$container['multilingualpress.save_plugin_settings_nonce'],
				$container['multilingualpress.asset_manager']
			);
		};

		$container['multilingualpress.plugin_settings_updater'] = function ( Container $container ) {

			return new PluginSettingsUpdater(
				$container['multilingualpress.module_manager'],
				$container['multilingualpress.save_plugin_settings_nonce'],
				$container['multilingualpress.plugin_settings_page']
			);
		};

		$container['multilingualpress.relationships_site_setting'] = function ( Container $container ) {

			return new RelationshipsSiteSetting(
				$container['multilingualpress.site_settings_repository'],
				$container['multilingualpress.site_relations']
			);
		};

		$container->share( 'multilingualpress.request', function () {

			return new ConditionalAwareRequest();
		} );

		$container['multilingualpress.save_plugin_settings_nonce'] = function () {

			return new WPNonce( 'save_plugin_settings' );
		};

		$container['multilingualpress.save_site_settings_nonce'] = function () {

			return new WPNonce( 'save_site_settings' );
		};

		$container['multilingualpress.site_data_deletor'] = function ( Container $container ) {

			return new SiteDataDeletor(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.site_relations'],
				$container['multilingualpress.site_settings_repository']
			);
		};

		$container['multilingualpress.site_settings'] = function ( Container $container ) {

			return new SiteSettings(
				$container['multilingualpress.site_settings_view']
			);
		};

		$container->share( 'multilingualpress.site_settings_repository', function ( Container $container ) {

			return new TypeSafeSiteSettingsRepository(
				$container['multilingualpress.site_relations']
			);
		} );

		$container['multilingualpress.site_settings_tab'] = function ( Container $container ) {

			return new EditSiteTab(
				$container['multilingualpress.site_settings_tab_data'],
				$container['multilingualpress.site_settings_tab_view']
			);
		};

		$container['multilingualpress.site_settings_tab_data'] = function () {

			return new EditSiteTabData(
				'multilingualpress',
				__( 'MultilingualPress', 'multilingual-press' ),
				'multilingualpress'
			);
		};

		$container['multilingualpress.site_settings_tab_view'] = function ( Container $container ) {

			return new SiteSettingsTabView(
				$container['multilingualpress.site_settings_tab_data'],
				new SiteSettingsSectionView( $container['multilingualpress.site_settings'] ),
				$container['multilingualpress.save_site_settings_nonce']
			);
		};

		$container['multilingualpress.site_settings_update_request_handler'] = function ( Container $container ) {

			return new SiteSettingsUpdateRequestHandler(
				$container['multilingualpress.site_settings_updater'],
				$container['multilingualpress.save_site_settings_nonce']
			);
		};

		$container['multilingualpress.site_settings_updater'] = function ( Container $container ) {

			return new SiteSettingsUpdater(
				$container['multilingualpress.site_settings_repository'],
				$container['multilingualpress.languages']
			);
		};

		$container['multilingualpress.site_settings_view'] = function ( Container $container ) {

			return SiteSettingMultiView::from_view_models( [
				$container['multilingualpress.language_site_setting'],
				$container['multilingualpress.alternative_language_title_site_setting'],
				$container['multilingualpress.flag_image_url_site_setting'],
				$container['multilingualpress.relationships_site_setting'],
			] );
		};
	}

	/**
	 * Bootstraps the registered services.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function bootstrap( Container $container ) {

		$properties = $container['multilingualpress.properties'];

		$plugin_dir_path = rtrim( $properties->plugin_dir_path(), '/' );

		$plugin_dir_url = rtrim( $properties->plugin_dir_url(), '/' );

		$container['multilingualpress.internal_locations']
			->add(
				'plugin',
				$plugin_dir_path,
				$plugin_dir_url
			)
			->add(
				'css',
				"{$plugin_dir_path}/assets/css",
				"{$plugin_dir_url}/assets/css"
			)
			->add(
				'js',
				"{$plugin_dir_path}/assets/js",
				"{$plugin_dir_url}/assets/js"
			)
			->add(
				'images',
				"{$plugin_dir_path}/assets/images",
				"{$plugin_dir_url}/assets/images"
			)
			->add(
				'flags',
				"{$plugin_dir_path}/assets/images/flags",
				"{$plugin_dir_url}/assets/images/flags"
			);

		load_plugin_textdomain( 'multilingual-press' );

		$setting_page = $container['multilingualpress.plugin_settings_page'];

		add_action( 'plugins_loaded', [ $setting_page, 'register' ], 8 );

		add_action(
			'admin_post_' . PluginSettingsUpdater::ACTION,
			[ $container['multilingualpress.plugin_settings_updater'], 'update_settings' ]
		);

		( new ActionLink(
			'settings',
			'<a href="' . esc_url( $setting_page->url() ) . '">' . __( 'Settings', 'multilingual-press' ) . '</a>'
		) )->register( 'network_admin_plugin_action_links_' . $properties->plugin_base_name() );

		add_action( 'delete_blog', [ $container['multilingualpress.site_data_deletor'], 'delete_site_data' ] );

		if ( is_admin() ) {
			global $pagenow;

			add_action(
				'admin_post_' . SiteSettingsUpdateRequestHandler::ACTION,
				[ $container['multilingualpress.site_settings_update_request_handler'], 'handle_post_request' ]
			);

			if ( 'sites.php' === $pagenow ) {
				( new SitesListTableColumn(
					'multilingualpress.relationships',
					__( 'Relationships', 'multilingual-press' ),
					function ( $id, $site_id ) {

						switch_to_blog( $site_id );
						$sites = get_available_language_names();
						restore_current_blog();
						unset( $sites[ $site_id ] );

						return $sites
							? sprintf(
								'<div class="mlp-site-relations">%s</div>',
								implode( '<br>', array_map( 'esc_html', $sites ) )
							)
							: __( 'none', 'multilingual-press' );
					}
				) )->register();

				( new SitesListTableColumn(
					'multilingualpress.site_language',
					__( 'Site Language', 'multilingual-press' ),
					function ( $id, $site_id ) {

						$language = get_site_language( $site_id );

						return '' === $language
							? __( 'none', 'multilingual-press' )
							: sprintf(
								'<div class="mlp-site-language">%s</div>',
								esc_html( get_language_field_by_http_code(
									str_replace( '_', '-', $language )
								) )
							);
					}
				) )->register();
			}

			if ( is_network_admin() ) {
				$container['multilingualpress.site_settings_tab']->register();

				$new_site_settings = $container['multilingualpress.new_site_settings'];

				add_action( 'network_site_new_form', function ( $site_id ) use ( $new_site_settings ) {

					( new SiteSettingsSectionView( $new_site_settings ) )->render( $site_id );
				} );

				add_action(
					'wpmu_new_blog',
					[ $container['multilingualpress.site_settings_updater'], 'define_initial_settings' ]
				);
			}
		} else {
			$translations = $container['multilingualpress.translations'];

			add_action( 'template_redirect', function () use ( $translations ) {

				if ( ! is_paged() ) {
					( new FrontEnd\AlternateLanguageHTTPHeaders( $translations ) )->send();
				}
			} );
			add_action( 'wp_head', function () use ( $translations ) {

				if ( ! is_paged() ) {
					( new FrontEnd\AlternateLanguageHTMLLinkTags( $translations ) )->render();
				}
			} );

			add_filter( 'language_attributes', 'Inpsyde\\MultilingualPress\\replace_language_in_language_attributes' );
		}
	}
}
