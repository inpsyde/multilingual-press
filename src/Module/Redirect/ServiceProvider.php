<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\Admin\SitesListTableColumn;
use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\Setting\Site\SecureSiteSettingUpdater;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSetting;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingsSectionView;
use Inpsyde\MultilingualPress\Common\Setting\User\SecureUserSettingUpdater;
use Inpsyde\MultilingualPress\Common\Setting\User\UserSetting;
use Inpsyde\MultilingualPress\Core\Admin\NewSiteSettings;
use Inpsyde\MultilingualPress\Module\ActivationAwareModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\ActivationAwareness;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class ServiceProvider implements ActivationAwareModuleServiceProvider {

	use ActivationAwareness;

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
				$container['multilingualpress.accept_language_parser']
			);
		};

		$container['multilingualpress.noredirect_permalink_filter'] = function () {

			return new NoredirectPermalinkFilter();
		};

		$container['multilingualpress.noredirect_storage'] = function () {

			return new NoredirectSessionStorage();
		};

		$container['multilingualpress.redirect_request_validator'] = function ( Container $container ) {

			return new NoredirectAwareRedirectRequestValidator(
				$container['multilingualpress.redirect_settings_repository'],
				$container['multilingualpress.noredirect_storage']
			);
		};

		$container->share( 'multilingualpress.redirect_settings_repository', function () {

			return new TypeSafeSettingsRepository();
		} );

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
				$container['multilingualpress.save_redirect_user_setting_nonce']
			);
		};

		$container['multilingualpress.redirector'] = function ( Container $container ) {

			return new NoredirectAwareRedirector(
				$container['multilingualpress.language_negotiator'],
				$container['multilingualpress.noredirect_storage']
			);
		};

		$container['multilingualpress.save_redirect_site_setting_nonce'] = function () {

			return new WPNonce( 'save_redirect_site_setting' );
		};

		$container['multilingualpress.save_redirect_user_setting_nonce'] = function () {

			return new WPNonce( 'save_redirect_user_setting' );
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

		$this->on_activation( function () use ( $container ) {

			( new UserSetting(
				$container['multilingualpress.redirect_user_setting'],
				$container['multilingualpress.redirect_user_setting_updater']
			) )->register();

			if ( is_admin() ) {
				global $pagenow;

				if ( is_network_admin() ) {
					$redirect_site_setting = new SiteSetting(
						$container['multilingualpress.redirect_site_setting'],
						$container['multilingualpress.redirect_site_setting_updater']
					);

					// TODO: Adapt to make it display and save on Edit Site as well.
					$redirect_site_setting->register(
						SiteSettingsSectionView::ACTION_AFTER . '_' . NewSiteSettings::ID,
						// TODO: Adapt hook as soon as it is a class constant (see Mlp_Network_Site_Settings_Controller).
						'mlp_blogs_save_fields'
					);

					if ( 'sites.php' === $pagenow ) {
						( new SitesListTableColumn(
							'multilingualpress.redirect',
							__( 'Redirect', 'multilingual-press' ),
							function ( $id, $site_id ) {

								// TODO: Don't hard-code option name, use repository or class constant.
								return get_blog_option( $site_id, 'inpsyde_multilingual_redirect' )
									? '<span class="dashicons dashicons-yes"></span>'
									: '';
							}
						) )->register();
					}
				}
			} else {
				$container['multilingualpress.noredirect_permalink_filter']->enable();

				if (
					! ( defined( 'DOING_AJAX' ) && DOING_AJAX )
					&& $container['multilingualpress.redirect_request_validator']->is_valid()
				) {
					add_action( 'template_redirect', [ $container['multilingualpress.redirector'], 'redirect' ], 1 );
				}
			}
		} );
	}

	/**
	 * Registers the module at the module manager.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 *
	 * @return bool Whether or not the module was registerd successfully AND has been activated.
	 */
	public function register_module( ModuleManager $module_manager ) {

		return $module_manager->register_module( new Module( 'redirect', [
			'description' => __( 'Redirect visitors according to browser language settings.', 'multilingual-press' ),
			'name'        => __( 'Redirect', 'multilingual-press' ),
			'active'      => false,
		] ) );
	}
}
