<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\UserAdminLanguage;

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\Setting\User\UserSetting;
use Inpsyde\MultilingualPress\Common\Setting\User\SecureUserSettingUpdater;
use Inpsyde\MultilingualPress\Module\ActivationAwareModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\ActivationAwareness;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\UserAdminLanguage
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

		$container['multilingualpress.user_admin_language_locale_filter'] = function ( Container $container ) {

			return new LocaleFilter(
				$container['multilingualpress.user_admin_language_repository']
			);
		};

		$container['multilingualpress.user_admin_language_repository'] = function () {

			return new TypeSafeLanguageRepository();
		};

		$container['multilingualpress.user_admin_language_setting'] = function ( Container $container ) {

			return new Setting(
				LanguageRepository::META_KEY,
				$container['multilingualpress.save_user_admin_language_setting_nonce'],
				$container['multilingualpress.user_admin_language_repository']
			);
		};

		$container['multilingualpress.save_user_admin_language_setting_nonce'] = function () {

			return new WPNonce( 'save_user_admin_language_setting' );
		};

		$container['multilingualpress.user_admin_language_setting_updater'] = function ( Container $container ) {

			return new SecureUserSettingUpdater(
				LanguageRepository::META_KEY,
				$container['multilingualpress.save_user_admin_language_setting_nonce']
			);
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

		if ( ! is_admin() ) {
			return;
		}

		$this->on_activation( function () use ( $container ) {

			$asset_manager = $container['multilingualpress.asset_manager'];

			$locale_filter = $container['multilingualpress.user_admin_language_locale_filter'];

			$locale_filter->enable();

			add_action( 'admin_head-options-general.php', function () use ( $asset_manager, $locale_filter ) {

				unset( $GLOBALS['locale'] );
				$locale_filter->disable();
				$unfiltered_locale = get_locale();
				$locale_filter->enable();

				$asset_manager->enqueue_script_with_data(
					'multilingualpress-admin',
					'mlpUserBackEndLanguageSettings',
					[
						'locale' => 'en_US' === $unfiltered_locale ? '' : esc_js( $unfiltered_locale ),
					]
				);
			} );

			( new UserSetting(
				$container['multilingualpress.user_admin_language_setting'],
				$container['multilingualpress.user_admin_language_setting_updater']
			) )->register();
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

		global $wp_version;

		if ( version_compare( $wp_version, '4.7', '>=' ) ) {
			return false;
		}

		return $module_manager->register_module( new Module( 'user_admin_language', [
			'description' => __(
				'Let each user choose a preferred language for the back end of all connected sites. This does not affect the front end.',
				'multilingual-press'
			),
			'name'        => __( 'User Admin Language', 'multilingual-press' ),
			'active'      => false,
		] ) );
	}
}
