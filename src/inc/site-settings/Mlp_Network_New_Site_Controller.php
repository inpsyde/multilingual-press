<?php
/**
 * MultilingualPress New Site Controller
 *
 * Add new form-fields to site-new.php and save them to db
 *
 * @version 2014.07.22
 * @author  Inpsyde GmbH, ChriCo, toscho
 * @license GPL
 */
class Mlp_Network_New_Site_Controller {

	/**
	 * @var Mlp_Assets_Interface
	 */
	private $assets;

	/**
	 * Language API
	 *
	 * @var Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * Language API
	 *
	 * @var Mlp_Language_Api_Interface
	 */
	private $site_relation;

	/**
	 * Constructor
	 * @wp-hook plugins_loaded
	 * @param Mlp_Language_Api_Interface   $language_api
	 * @param Mlp_Site_Relations_Interface $site_relation
	 * @param Mlp_Assets_Interface         $assets
	 */
	public function __construct(
		Mlp_Language_Api_Interface $language_api,
		Mlp_Site_Relations_Interface $site_relation,
		Mlp_Assets_Interface $assets
	) {

		if ( ! is_network_admin() ) {
			return;
		}

		$this->language_api  = $language_api;
		$this->site_relation = $site_relation;
		$this->assets        = $assets;

		add_action( 'wpmu_new_blog', array( $this, 'update' ) );

		add_action( 'load-site-new.php', array( $this, 'provide_assets' ) );

		// TODO: Simplify, by deleting the template stuff, with the release of WordPress 4.5.0 + 2.
		$view = new Mlp_New_Site_View( $this->language_api );
		// Get the unaltered WordPress version.
		require ABSPATH . WPINC . '/version.php';
		/** @var string $wp_version */
		if ( version_compare( $wp_version, '4.5-alpha', '<' ) ) {
			add_action( 'admin_footer', array( $view, 'print_template' ) );
		} else {
			add_action( 'network_site_new_form', array( $view, 'render' ) );
		}
	}

	/**
	 * Combine all update actions.
	 *
	 * @param   int $blog_id
	 * @return  void
	 */
	public function update( $blog_id ) {

		$this->update_wplang( $blog_id );
		$this->update_language( $blog_id );
		$this->update_relation( $blog_id );
	}

	/**
	 * @param   int $blog_id
	 * @return  void
	 */
	private function update_language( $blog_id ) {

		$posted = $this->get_posted_language();

		if ( ! $posted ) {
			return;
		}

		$languages = (array) get_site_option( 'inpsyde_multilingual', array() );

		if ( empty( $languages[ $blog_id ] ) ) {
			$languages[ $blog_id ] = array();
		}

		$languages[ $blog_id ]['lang'] = str_replace( '-', '_', $posted );

		// Set alternative title
		$text = filter_input( INPUT_POST, 'inpsyde_multilingual_text' );
		if ( null !== $text ) {
			$languages[ $blog_id ]['text'] = (string) $text;
		}

		update_site_option( 'inpsyde_multilingual', $languages );
	}

	/**
	 * Get language from post request.
	 *
	 * @return bool|string
	 */
	private function get_posted_language() {

		$language = (string) filter_input( INPUT_POST, 'inpsyde_multilingual_lang' );
		if ( '' === $language ) {
			return false;
		}

		if ( '-1' === $language ) {
			return false;
		}

		return $language;
	}

	/**
	 * Update option WPLANG in DB for new sites.
	 *
	 * @param   int $blog_id
	 * @return  void
	 */
	private function update_wplang( $blog_id ) {

		$posted = $this->get_posted_language();

		if ( ! $posted ) {
			return;
		}

		// search for wp_locale where search = $http_name
		$search = array(
			'fields' => array(
				'wp_locale',
			),
			'where' => array(
				array(
					'field'     => 'http_name',
					'search'    => $posted,
				),
			),
		);

		$available_language = $this->language_api->get_db()->get_items( $search, OBJECT );

		// no results found? -> return
		if ( empty( $available_language ) ) {
			return;
		}

		// getting the first wp_locale
		$wp_locale = $available_language[0]->wp_locale;

		$available_lang_files = get_available_languages();

		if ( ! in_array( $wp_locale, $available_lang_files, true ) ) {
			return;
		}

		update_blog_option( $blog_id, 'WPLANG', $wp_locale );
	}

	/**
	 * Updating Site Relations for new sites.
	 *
	 * @param  int $blog_id
	 * @return int Number of affected rows.
	 */
	private function update_relation( $blog_id ) {

		$related_blogs = filter_input( INPUT_POST, 'related_blogs', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( ! $related_blogs ) {
			return 0;
		}

		$related_blogs = array_map( 'intval', $related_blogs );

		return $this->site_relation->set_relation( $blog_id, $related_blogs );
	}

	/**
	 * Takes care of the required assets being provided.
	 *
	 * @return void
	 */
	public function provide_assets() {

		$this->assets->provide( 'mlp-admin' );
	}
}
