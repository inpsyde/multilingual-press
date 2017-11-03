<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\Common\Admin\ActionLink;
use Inpsyde\MultilingualPress\Common\Admin\EditSiteTab;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUIRegistry;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPage;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageTab;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageTabData;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageTabDataAccess;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\Admin\SitesListTableColumn;
use Inpsyde\MultilingualPress\Common\AlternateLanguages;
use Inpsyde\MultilingualPress\Common\HTTP\FullRequestGlobalManipulator;
use Inpsyde\MultilingualPress\Common\HTTP\PHPServerRequest;
use Inpsyde\MultilingualPress\Common\HTTP\RequestGlobalsManipulator;
use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\ConditionalAwareWordPressRequestContext;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingMultiView;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingsSectionView;
use Inpsyde\MultilingualPress\Core\Admin\AlternativeLanguageTitleSiteSetting;
use Inpsyde\MultilingualPress\Core\Admin\LanguageSiteSetting;
use Inpsyde\MultilingualPress\Core\Admin\ModuleSettingsTabView;
use Inpsyde\MultilingualPress\Core\Admin\NewSiteSettings;
use Inpsyde\MultilingualPress\Core\Admin\PluginSettingsPageView;
use Inpsyde\MultilingualPress\Core\Admin\PluginSettingsUpdater;
use Inpsyde\MultilingualPress\Core\Admin\PostTypeSettingsTabView;
use Inpsyde\MultilingualPress\Core\Admin\RelationshipsSiteSetting;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettings;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsTabView;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsUpdater;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsUpdateRequestHandler;
use Inpsyde\MultilingualPress\Core\Admin\TaxonomySettingsTabView;
use Inpsyde\MultilingualPress\Core\Admin\TypeSafeSiteSettingsRepository;
use Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguageController;
use Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguageHTMLLinkTagRenderer;
use Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguageHTTPHeaderRenderer;
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

		$container['multilingualpress.base_path_adapter'] = function () {

			return new CachingBasePathAdapter();
		};

		$container->share( 'multilingualpress.http_post_request_globals_manipulator', function () {

			return new FullRequestGlobalManipulator( RequestGlobalsManipulator::METHOD_POST );
		} );

		$container->share( 'multilingualpress.internal_locations', function () {

			return new InternalLocations();
		} );

		$container->share( 'multilingualpress.server_request', function () {

			return new PHPServerRequest();
		} );

		$container['multilingualpress.site_data_deletor'] = function ( Container $container ) {

			return new SiteDataDeletor(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.site_relations'],
				$container['multilingualpress.site_settings_repository']
			);
		};

		$container->share( 'multilingualpress.wordpress_request_context', function () {

			return new ConditionalAwareWordPressRequestContext();
		} );

		$this->register_admin( $container );
		$this->register_front_end( $container );
	}

	/**
	 * Registers the provided admin services on the given container.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_admin( Container $container ) {

		$container['multilingualpress.alternative_language_title_site_setting'] = function ( Container $container ) {

			return new AlternativeLanguageTitleSiteSetting(
				$container['multilingualpress.site_settings_repository']
			);
		};

		$container['multilingualpress.language_site_setting'] = function ( Container $container ) {

			return new LanguageSiteSetting(
				$container['multilingualpress.site_settings_repository'],
				$container['multilingualpress.languages']
			);
		};

		$container->share( 'multilingualpress.meta_box_ui_registry', function () {

			return new MetaBoxUIRegistry();
		} );

		$container['multilingualpress.module_manager'] = function () {

			return new Module\NetworkOptionModuleManager( Module\ModuleManager::OPTION );
		};

		$container['multilingualpress.module_settings_tab_data'] = function () {

			return new SettingsPageTabData(
				'modules',
				__( 'Modules', 'multilingualpress' ),
				'modules'
			);
		};

		$container['multilingualpress.module_settings_tab_view'] = function ( Container $container ) {

			return new ModuleSettingsTabView(
				$container['multilingualpress.module_manager']
			);
		};

		$container['multilingualpress.new_site_settings'] = function ( Container $container ) {

			return new NewSiteSettings(
				$container['multilingualpress.site_settings_view'],
				$container['multilingualpress.asset_manager']
			);
		};

		$container['multilingualpress.plugin_settings_page'] = function ( Container $container ) {

			return SettingsPage::with_parent(
				SettingsPage::ADMIN_NETWORK,
				SettingsPage::PARENT_NETWORK_SETTINGS,
				__( 'MultilingualPress', 'multilingualpress' ),
				__( 'MultilingualPress', 'multilingualpress' ),
				'manage_network_options',
				'multilingualpress',
				$container['multilingualpress.plugin_settings_page_view']
			);
		};

		$container['multilingualpress.plugin_settings_page_tabs'] = function ( Container $container ) {

			$tabs = [
				[
					'multilingualpress.module_settings_tab_data',
					'multilingualpress.module_settings_tab_view',
				],
				[
					'multilingualpress.post_type_settings_tab_data',
					'multilingualpress.post_type_settings_tab_view',
				],
				[
					'multilingualpress.taxonomy_settings_tab_data',
					'multilingualpress.taxonomy_settings_tab_view',
				],
			];

			return array_reduce( $tabs, function ( array $tabs, array $tab ) use ( $container ) {

				$data = $container[ $tab[0] ] ?? null;
				if ( ! $data instanceof SettingsPageTabDataAccess ) {
					return $tabs;
				}

				$view = $container[ $tab[1] ] ?? null;
				if ( ! $view instanceof SettingsPageView ) {
					return $tabs;
				}

				$tabs[ $data->id() ] = new SettingsPageTab( $data, $view );

				return $tabs;
			}, [] );
		};

		$container['multilingualpress.plugin_settings_page_view'] = function ( Container $container ) {

			return new PluginSettingsPageView(
				$container['multilingualpress.save_plugin_settings_nonce'],
				$container['multilingualpress.asset_manager'],
				$container['multilingualpress.server_request'],
				$container['multilingualpress.plugin_settings_page_tabs']
			);
		};

		$container['multilingualpress.plugin_settings_updater'] = function ( Container $container ) {

			return new PluginSettingsUpdater(
				$container['multilingualpress.module_manager'],
				$container['multilingualpress.save_plugin_settings_nonce'],
				$container['multilingualpress.server_request']
			);
		};

		$container['multilingualpress.post_type_settings_tab_data'] = function () {

			return new SettingsPageTabData(
				'post-types',
				__( 'Post Types', 'multilingualpress' ),
				'post-types'
			);
		};

		$container['multilingualpress.post_type_settings_tab_view'] = function ( Container $container ) {

			return new PostTypeSettingsTabView();
		};

		$container['multilingualpress.relationships_site_setting'] = function ( Container $container ) {

			return new RelationshipsSiteSetting(
				$container['multilingualpress.site_settings_repository'],
				$container['multilingualpress.site_relations']
			);
		};

		$container['multilingualpress.save_plugin_settings_nonce'] = function () {

			return new WPNonce( 'save_plugin_settings' );
		};

		$container['multilingualpress.save_site_settings_nonce'] = function () {

			return new WPNonce( 'save_site_settings' );
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
				new SettingsPageTab(
					$container['multilingualpress.site_settings_tab_data'],
					$container['multilingualpress.site_settings_tab_view']
				)
			);
		};

		$container['multilingualpress.site_settings_tab_data'] = function () {

			return new SettingsPageTabData(
				'multilingualpress',
				__( 'MultilingualPress', 'multilingualpress' ),
				'multilingualpress',
				'manage_sites'
			);
		};

		$container['multilingualpress.site_settings_tab_view'] = function ( Container $container ) {

			return new SiteSettingsTabView(
				$container['multilingualpress.site_settings_tab_data'],
				new SiteSettingsSectionView( $container['multilingualpress.site_settings'] ),
				$container['multilingualpress.server_request'],
				$container['multilingualpress.save_site_settings_nonce']
			);
		};

		$container['multilingualpress.site_settings_update_request_handler'] = function ( Container $container ) {

			return new SiteSettingsUpdateRequestHandler(
				$container['multilingualpress.site_settings_updater'],
				$container['multilingualpress.server_request'],
				$container['multilingualpress.save_site_settings_nonce']
			);
		};

		$container['multilingualpress.site_settings_updater'] = function ( Container $container ) {

			return new SiteSettingsUpdater(
				$container['multilingualpress.site_settings_repository'],
				$container['multilingualpress.languages'],
				$container['multilingualpress.server_request']
			);
		};

		$container['multilingualpress.site_settings_view'] = function ( Container $container ) {

			return SiteSettingMultiView::from_view_models( [
				$container['multilingualpress.language_site_setting'],
				$container['multilingualpress.alternative_language_title_site_setting'],
				$container['multilingualpress.relationships_site_setting'],
			] );
		};

		$container['multilingualpress.taxonomy_settings_tab_data'] = function () {

			return new SettingsPageTabData(
				'taxonomies',
				__( 'Taxonomies', 'multilingualpress' ),
				'taxonomies'
			);
		};

		$container['multilingualpress.taxonomy_settings_tab_view'] = function ( Container $container ) {

			return new TaxonomySettingsTabView();
		};
	}

	/**
	 * Registers the provided front-end services on the given container.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_front_end( Container $container ) {

		$container['multilingualpress.alternate_language_controller'] = function () {

			return new AlternateLanguageController();
		};

		$container['multilingualpress.alternate_language_html_link_tag_renderer'] = function ( Container $container ) {

			return new AlternateLanguageHTMLLinkTagRenderer(
				$container['multilingualpress.alternate_languages']
			);
		};

		$container['multilingualpress.alternate_language_http_header_renderer'] = function ( Container $container ) {

			return new AlternateLanguageHTTPHeaderRenderer(
				$container['multilingualpress.alternate_languages']
			);
		};

		$container->share( 'multilingualpress.alternate_languages', function ( Container $container ) {

			return new AlternateLanguages(
				$container['multilingualpress.translations']
			);
		} );
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
			);

		load_plugin_textdomain( 'multilingualpress' );

		add_action( 'delete_blog', [ $container['multilingualpress.site_data_deletor'], 'delete_site_data' ] );

		if ( is_admin() ) {
			$this->bootstrap_admin( $container );

			return;
		}

		$this->bootstrap_front_end( $container );
	}

	/**
	 * Bootstraps the registered admin services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function bootstrap_admin( Container $container ) {

		$properties = $container['multilingualpress.properties'];

		$settings_page = $container['multilingualpress.plugin_settings_page'];

		add_action( 'plugins_loaded', [ $settings_page, 'register' ], 8 );

		add_action(
			'admin_post_' . PluginSettingsUpdater::ACTION,
			[ $container['multilingualpress.plugin_settings_updater'], 'update_settings' ]
		);

		( new ActionLink(
			'settings',
			'<a href="' . esc_url( $settings_page->url() ) . '">' . __( 'Settings', 'multilingualpress' ) . '</a>'
		) )->register( 'network_admin_plugin_action_links_' . $properties->plugin_base_name() );

		add_action(
			'admin_post_' . SiteSettingsUpdateRequestHandler::ACTION,
			[ $container['multilingualpress.site_settings_update_request_handler'], 'handle_post_request' ]
		);

		if ( is_network_admin() ) {
			$this->bootstrap_network_admin( $container );
		}
	}

	/**
	 * Bootstraps the registered network-admin services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function bootstrap_network_admin( Container $container ) {

		global $pagenow;

		$container['multilingualpress.site_settings_tab']->register();

		$new_site_settings = $container['multilingualpress.new_site_settings'];

		add_action( 'network_site_new_form', function ( $site_id ) use ( $new_site_settings ) {

			( new SiteSettingsSectionView( $new_site_settings ) )->render( (int) $site_id );
		} );

		add_action(
			'wpmu_new_blog',
			[ $container['multilingualpress.site_settings_updater'], 'define_initial_settings' ]
		);

		if ( 'sites.php' === $pagenow ) {
			( new SitesListTableColumn(
				'multilingualpress.relationships',
				__( 'Relationships', 'multilingualpress' ),
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
						: __( 'none', 'multilingualpress' );
				}
			) )->register();

			( new SitesListTableColumn(
				'multilingualpress.site_language',
				__( 'Site Language', 'multilingualpress' ),
				function ( $id, $site_id ) {

					$language = get_site_language( $site_id );

					return '' === $language
						? __( 'none', 'multilingualpress' )
						: sprintf(
							'<div class="mlp-site-language">%s</div>',
							esc_html( get_language_field_by_http_code(
								str_replace( '_', '-', $language )
							) )
						);
				}
			) )->register();
		}
	}

	/**
	 * Bootstraps the registered front-end services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function bootstrap_front_end( Container $container ) {

		$alternate_language_controller = $container['multilingualpress.alternate_language_controller'];
		$alternate_language_controller->register_renderer(
			$container['multilingualpress.alternate_language_html_link_tag_renderer'],
			'wp_head'
		);
		$alternate_language_controller->register_renderer(
			$container['multilingualpress.alternate_language_http_header_renderer'],
			'template_redirect',
			11
		);

		add_filter( 'language_attributes', 'Inpsyde\\MultilingualPress\\replace_language_in_language_attributes' );
	}
}
