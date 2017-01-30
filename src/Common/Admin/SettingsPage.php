<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Settings page.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
class SettingsPage {

	/**
	 * Settings page admin type.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const ADMIN_NETWORK = 1;

	/**
	 * Settings page admin type.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const ADMIN_SITE = 0;

	/**
	 * Settings page admin type.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const ADMIN_USER = 2;

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_APPEARANCE = 'themes.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_COMMENTS = 'edit-comments.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_DASHBOARD = 'index.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_LINKS = 'link-manager.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_MEDIA = 'upload.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_NETWORK_SETTINGS = 'settings.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_PAGES = 'edit.php?post_type=page';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_PLUGINS = 'plugins.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_POSTS = 'edit.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_SETTINGS = 'options-general.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_SITES = 'sites.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_THEMES = 'themes.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_TOOLS = 'tools.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_USER_PROFILE = 'profile.php';

	/**
	 * Parent page value.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PARENT_USERS = 'users.php';

	/**
	 * @var int
	 */
	private $admin;

	/**
	 * @var string
	 */
	private $capability;

	/**
	 * @var string
	 */
	private $hookname = '';

	/**
	 * @var string
	 */
	private $icon;

	/**
	 * @var string
	 */
	private $menu_title;

	/**
	 * @var string
	 */
	private $parent = '';

	/**
	 * @var int|null
	 */
	private $position;

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var SettingsPageView
	 */
	private $view;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int              $admin      Admin type. Use the class constants.
	 * @param string           $title      Title on the page itself.
	 * @param string           $menu_title Title in the admin menu.
	 * @param string           $capability Capability required to view the settings page.
	 * @param string           $slug       Page slug used in the URL.
	 * @param SettingsPageView $view       View object.
	 * @param string           $icon       Optinoal. Icon URL. Defaults to empty string.
	 * @param int|null         $position   Optional. Position in the admin menu. Defaults to null.
	 */
	public function __construct(
		$admin,
		$title,
		$menu_title,
		$capability,
		$slug,
		SettingsPageView $view,
		$icon = '',
		$position = null
	) {

		$this->admin = (int) $admin;

		$this->title = (string) $title;

		$this->menu_title = (string) $menu_title;

		$this->capability = (string) $capability;

		$this->slug = (string) $slug;

		$this->view = $view;

		$this->icon = (string) $icon;

		$this->position = is_numeric( $position ) ? (int) $position : null;
	}

	/**
	 * Returns a new settings page object, instantiated according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param int              $admin      Admin type. Use the class constants.
	 * @param string           $parent     Parent page. Use the available class constants.
	 * @param string           $title      Title on the page itself.
	 * @param string           $menu_title Title in the admin menu.
	 * @param string           $capability Capability required to view the settings page.
	 * @param string           $slug       Page slug used in the URL.
	 * @param SettingsPageView $view       View object.
	 *
	 * @return static Settings page object.
	 */
	public static function with_parent(
		$admin,
		$parent,
		$title,
		$menu_title,
		$capability,
		$slug,
		SettingsPageView $view
	) {

		$settings_page = new static(
			$admin,
			$title,
			$menu_title,
			$capability,
			$slug,
			$view
		);

		$settings_page->parent = (string) $parent;

		return $settings_page;
	}

	/**
	 * Returns the capability.
	 *
	 * @since 3.0.0
	 *
	 * @return string The capability.
	 */
	public function capability() {

		return $this->capability;
	}

	/**
	 * Returns the hookname.
	 *
	 * @since 3.0.0
	 *
	 * @return string The hookname.
	 */
	public function hookname() {

		return $this->hookname;
	}

	/**
	 * Registers the settings page.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the settings page was registered successfully.
	 */
	public function register() {

		$action = $this->get_action();
		if ( ! $action ) {
			return false;
		}

		return add_action( $action, function () {

			$this->hookname = call_user_func_array( $this->get_callback(), $this->get_callback_args() );
		} );
	}

	/**
	 * Returns the slug.
	 *
	 * @since 3.0.0
	 *
	 * @return string The slug.
	 */
	public function slug() {

		return $this->slug;
	}

	/**
	 * Returns the title.
	 *
	 * @since 3.0.0
	 *
	 * @return string The title.
	 */
	public function title() {

		return $this->title;
	}

	/**
	 * Returns the full URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string URL.
	 */
	public function url() {

		if ( ! isset( $this->url ) ) {
			$url = add_query_arg( 'page', $this->slug, $this->parent ?: 'admin.php' );

			switch ( $this->admin ) {
				case static::ADMIN_NETWORK:
					$this->url = network_admin_url( $url );
					break;

				case static::ADMIN_SITE:
					$this->url = admin_url( $url );
					break;

				case static::ADMIN_USER:
					$this->url = user_admin_url( $url );
					break;
			}
		}

		return $this->url;
	}

	/**
	 * Returns the action for registering the page.
	 *
	 * @return string.
	 */
	private function get_action() {

		switch ( $this->admin ) {
			case static::ADMIN_NETWORK:
				return 'network_admin_menu';

			case static::ADMIN_SITE:
				return 'admin_menu';

			case static::ADMIN_USER:
				return 'user_admin_menu';
		}

		return '';
	}

	/**
	 * Returns the callback for adding the page to the admin menu.
	 *
	 * @return callable Callback for adding the page to the admin menu.
	 */
	private function get_callback() {

		return $this->parent ? 'add_submenu_page' : 'add_menu_page';
	}

	/**
	 * Returns the callback args for adding the page to the admin menu.
	 *
	 * @return array Callback args for adding the page to the admin menu.
	 */
	private function get_callback_args() {

		return array_diff_key(
			[
				'parent'     => $this->parent,
				'title'      => $this->title,
				'menu_title' => $this->menu_title,
				'capability' => $this->capability,
				'slug'       => $this->slug,
				'callback'   => [ $this->view, 'render' ],
				'icon'       => $this->icon,
				'position'   => $this->position,
			],
			$this->parent ? [ 'icon', 'position' ] : [ 'parent' ]
		);
	}
}
