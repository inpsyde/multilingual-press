<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\Admin\SitesListTableColumn;
use Inpsyde\MultilingualPress\Common\AlternateLanguages;
use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\Setting\Site\SecureSiteSettingUpdater;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSetting;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingsSectionView;
use Inpsyde\MultilingualPress\Common\Setting\User\SecureUserSettingUpdater;
use Inpsyde\MultilingualPress\Common\Setting\User\UserSetting;
use Inpsyde\MultilingualPress\Core\Admin\NewSiteSettings;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettings;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsUpdater;
use Inpsyde\MultilingualPress\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class ServiceProvider implements ModuleServiceProvider {

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

		$container['multilingualpress.accept_language_parser'] = function () {

			return new AcceptLanguageParser();
		};

		$container['multilingualpress.language_negotiator'] = function ( Container $container ) {

			return new PriorityAwareLanguageNegotiator(
				$container['multilingualpress.translations'],
				$container['multilingualpress.server_request'],
				$container['multilingualpress.accept_language_parser']
			);
		};

		$container['multilingualpress.noredirect_permalink_filter'] = function () {

			return new NoredirectPermalinkFilter();
		};

		$container['multilingualpress.noredirect_storage'] = function () {

			// @codingStandardsIgnoreStart
			/*
			 * TODO for logged in user, when using external object cache, we used to return
			 * `NoredirectObjectCacheStorage` here.
			 * Think about adding cached storage again, when cache handling will be refactored.
			 */
			// @codingStandardsIgnoreEnd

			return new NoredirectSessionStorage();
		};

		$container['multilingualpress.redirect_request_validator'] = function ( Container $container ) {

			return new NoredirectAwareRedirectRequestValidator(
				$container['multilingualpress.redirect_settings_repository'],
				$container['multilingualpress.noredirect_storage']
			);
		};

		$container->share(
            'multilingualpress.redirect_settings_repository', function () {

			return new TypeSafeSettingsRepository();
            }
        );

		$container['multilingualpress.redirect_site_setting'] = function ( Container $container ) {

			return new RedirectSiteSetting(
				SettingsRepository::OPTION_SITE,
				$container['multilingualpress.save_redirect_site_setting_nonce'],
				$container['multilingualpress.redirect_settings_repository']
			);
		};

		$container['multilingualpress.redirect_site_setting_updater'] = function ( Container $container ) {

			return new SecureSiteSettingUpdater(
				SettingsRepository::OPTION_SITE,
				$container['multilingualpress.server_request'],
				$container['multilingualpress.save_redirect_site_setting_nonce']
			);
		};

		$container['multilingualpress.redirect_user_setting'] = function ( Container $container ) {

			return new RedirectUserSetting(
				SettingsRepository::META_KEY_USER,
				$container['multilingualpress.save_redirect_user_setting_nonce'],
				$container['multilingualpress.redirect_settings_repository']
			);
		};

		$container['multilingualpress.redirect_user_setting_updater'] = function ( Container $container ) {

			return new SecureUserSettingUpdater(
				SettingsRepository::META_KEY_USER,
				$container['multilingualpress.server_request'],
				$container['multilingualpress.save_redirect_user_setting_nonce']
			);
		};

		$container['multilingualpress.redirector'] = function ( Container $container ) {

			/**
			 * Filters the redirector type.
			 *
			 * @since 3.0.0
			 *
			 * @param string $type Redirector type.
			 */
			$type = apply_filters( Redirector::FILTER_TYPE, Redirector::TYPE_PHP );
			switch ( strtoupper( $type ) ) {
				case Redirector::TYPE_JAVASCRIPT:
					return new NoredirectAwareJavaScriptRedirector(
						$container['multilingualpress.language_negotiator'],
						$container['multilingualpress.asset_manager']
					);

				case Redirector::TYPE_PHP:
				default:
					return new NoredirectAwareRedirector(
						$container['multilingualpress.language_negotiator'],
						$container['multilingualpress.noredirect_storage'],
						$container['multilingualpress.server_request']
					);
			}
		};

		$container['multilingualpress.save_redirect_site_setting_nonce'] = function () {

			return new WPNonce( 'save_redirect_site_setting' );
		};

		$container['multilingualpress.save_redirect_user_setting_nonce'] = function () {

			return new WPNonce( 'save_redirect_user_setting' );
		};
	}

	/**
	 * Registers the module at the module manager.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 *
	 * @return bool Whether or not the module was registered successfully AND has been activated.
	 */
	public function register_module( ModuleManager $module_manager ): bool {

		return $module_manager->register_module(
            new Module(
                'redirect', [
                 'description' => __( 'Redirect visitors according to browser language settings.', 'multilingualpress' ),
                 'name'        => __( 'Redirect', 'multilingualpress' ),
                 'active'      => false,
                ]
            )
        );
	}

	/**
	 * Performs various tasks on module activation.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function activate_module( Container $container ) {

		( new UserSetting(
			$container['multilingualpress.redirect_user_setting'],
			$container['multilingualpress.redirect_user_setting_updater']
		) )->register();

		if ( is_admin() ) {
			$this->activate_module_for_admin( $container );

			return;
		}

		$this->activate_module_for_front_end( $container );
	}

	/**
	 * Performs various admin-specific tasks on module activation.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function activate_module_for_admin( Container $container ) {

		( new SiteSetting(
			$container['multilingualpress.redirect_site_setting'],
			$container['multilingualpress.redirect_site_setting_updater']
		) )->register(
			SiteSettingsSectionView::ACTION_AFTER . '_' . SiteSettings::ID,
			SiteSettingsUpdater::ACTION_UPDATE_SETTINGS
		);

		if ( is_network_admin() ) {
			$this->activate_module_for_network_admin( $container );
		}
	}

	/**
	 * Performs various admin-specific tasks on module activation.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function activate_module_for_network_admin( Container $container ) {

		( new SiteSetting(
			$container['multilingualpress.redirect_site_setting'],
			$container['multilingualpress.redirect_site_setting_updater']
		) )->register(
			SiteSettingsSectionView::ACTION_AFTER . '_' . NewSiteSettings::ID,
			SiteSettingsUpdater::ACTION_DEFINE_INITIAL_SETTINGS
		);

		if ( 'sites.php' !== ( $GLOBALS['pagenow'] ?? '' ) ) {
			return;
		}

		$redirect_settings_repository = $container['multilingualpress.redirect_settings_repository'];

		$render_callback = function ( $id, $site_id ) use ( $redirect_settings_repository ) {

			return $redirect_settings_repository->get_site_setting( (int) $site_id )
				? '<span class="dashicons dashicons-yes"></span>'
				: '';
		};

		$site_list_column = new SitesListTableColumn(
			'multilingualpress.redirect',
			__( 'Redirect', 'multilingualpress' ),
			$render_callback
		);

		$site_list_column->register();
	}

	/**
	 * Performs various admin-specific tasks on module activation.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function activate_module_for_front_end( Container $container ) {

		$container['multilingualpress.asset_manager']
			->register_script(
				$container['multilingualpress.asset_factory']->create_internal_script(
					'multilingualpress-redirect',
					'redirect.js'
				)
			);

		$filter = $container['multilingualpress.noredirect_permalink_filter'];
		$filter->enable();
		add_action( AlternateLanguages::FILTER_URL, [ $filter, 'remove_noredirect_query_argument' ] );

		if ( $container['multilingualpress.redirect_request_validator']->is_valid() ) {
			add_action( 'wp_loaded', [ $container['multilingualpress.redirector'], 'redirect' ], 0 );
		}
	}
}
