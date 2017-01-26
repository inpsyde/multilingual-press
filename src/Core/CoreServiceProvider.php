<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\Common\Admin\ActionLink;
use Inpsyde\MultilingualPress\Common\Admin\AdminNotice;
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
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsUpdater;
use Inpsyde\MultilingualPress\Core\Admin\TypeSafeSiteSettingsRepository;
use Inpsyde\MultilingualPress\Module;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;

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
				$container['multilingualpress.new_site_settings_view']
			);
		};

		$container['multilingualpress.new_site_settings_view'] = function ( Container $container ) {

			return SiteSettingMultiView::from_view_models( [
				$container['multilingualpress.language_site_setting'],
				$container['multilingualpress.alternative_language_title_site_setting'],
				$container['multilingualpress.flag_image_url_site_setting'],
				$container['multilingualpress.relationships_site_setting'],
			] );
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
				$container['multilingualpress.update_plugin_settings_nonce'],
				$container['multilingualpress.asset_manager']
			);
		};

		$container['multilingualpress.plugin_settings_updater'] = function ( Container $container ) {

			return new PluginSettingsUpdater(
				$container['multilingualpress.module_manager'],
				$container['multilingualpress.update_plugin_settings_nonce'],
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

		$container->share( 'multilingualpress.site_settings_repository', function ( Container $container ) {

			return new TypeSafeSiteSettingsRepository(
				$container['multilingualpress.site_relations']
			);
		} );

		$container['multilingualpress.site_settings_updater'] = function ( Container $container ) {

			return new SiteSettingsUpdater(
				$container['multilingualpress.site_settings_repository'],
				$container['multilingualpress.languages']
			);
		};

		$container['multilingualpress.update_plugin_settings_nonce'] = function () {

			return new WPNonce( 'update_plugin_settings' );
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
				"$plugin_dir_path/assets/css",
				"$plugin_dir_url/assets/css"
			)
			->add(
				'js',
				"$plugin_dir_path/assets/js",
				"$plugin_dir_url/assets/js"
			)
			->add(
				'images',
				"$plugin_dir_path/assets/images",
				"$plugin_dir_url/assets/images"
			)
			->add(
				'flags',
				"$plugin_dir_path/assets/images/flags",
				"$plugin_dir_url/assets/images/flags"
			);

		load_plugin_textdomain( 'multilingual-press' );

		$setting_page = $container['multilingualpress.plugin_settings_page'];

		add_action( 'plugins_loaded', [ $setting_page, 'register' ], 8 );

		add_action(
			'admin_post_' . PluginSettingsUpdater::ACTION,
			[ $container['multilingualpress.plugin_settings_updater'], 'update_settings' ]
		);

		add_action( 'network_admin_notices', function () use ( $setting_page ) {

			if (
				isset( $_GET['message'] )
				&& isset( $GLOBALS['hook_suffix'] )
				&& $setting_page->hookname() === $GLOBALS['hook_suffix']
			) {
				( new AdminNotice( '<p>' . __( 'Settings saved.', 'multilingual-press' ) . '</p>' ) )->render();
			}
		} );

		( new ActionLink(
			'settings',
			'<a href="' . esc_url( $setting_page->url() ) . '">' . __( 'Settings', 'multilingual-press' ) . '</a>'
		) )->register( 'network_admin_plugin_action_links_' . $properties->plugin_base_name() );

		// TODO: Bundle the following block in some PluginDataDeletor or so...
		$content_relations = $container['multilingualpress.content_relations'];
		$site_relations = $container['multilingualpress.site_relations'];
		$site_settings_repository = $container['multilingualpress.site_settings_repository'];
		add_action( 'delete_blog', function ( $site_id ) use ( $content_relations, $site_relations, $site_settings_repository ) {

			$content_relations->delete_relations_for_site( $site_id );

			$site_relations->delete_relation( $site_id );

			$settings = $site_settings_repository->get_settings();
			if ( isset( $settings[ $site_id ] ) ) {
				unset( $settings[ $site_id ] );

				$site_settings_repository->set_settings( $settings );
			}
		} );

		if ( is_admin() ) {
			global $pagenow;

			$site_settings_updater = $container['multilingualpress.site_settings_updater'];
			// TODO: Handle site settings update via AJAX (once implemented).

			if ( 'sites.php' === $pagenow ) {
				( new SitesListTableColumn(
					'multilingualpress.relationships',
					__( 'Relationships', 'multilingual-press' ),
					function ( $id, $site_id ) {

						switch_to_blog( $site_id );
						$sites = \Inpsyde\MultilingualPress\get_available_language_names();
						restore_current_blog();
						unset( $sites[ $site_id ] );

						return $sites
							? sprintf(
								'<div class="mlp-site-relations">%s</div>',
								join( '<br>', array_map( 'esc_html', $sites ) )
							)
							: __( 'none', 'multilingual-press' );
					}
				) )->register();

				( new SitesListTableColumn(
					'multilingualpress.site_language',
					__( 'Site Language', 'multilingual-press' ),
					function ( $id, $site_id ) {

						$language = \Inpsyde\MultilingualPress\get_site_language( $site_id );

						return '' === $language
							? __( 'none', 'multilingual-press' )
							: sprintf(
								'<div class="mlp-site-language">%s</div>',
								esc_html( \Inpsyde\MultilingualPress\get_language_by_http_name(
									str_replace( '_', '-', $language )
								) )
							);
					}
				) )->register();
			}

			if ( is_network_admin() ) {

				$new_site_settings = $container['multilingualpress.new_site_settings'];

				add_action( 'network_site_new_form', function ( $ite_id ) use ( $new_site_settings ) {

					( new SiteSettingsSectionView( $new_site_settings ) )->render( $ite_id );
				} );

				add_action( 'wpmu_new_blog', [ $site_settings_updater, 'define_initial_settings' ] );
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
