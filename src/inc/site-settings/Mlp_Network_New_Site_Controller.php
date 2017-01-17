<?php

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\API\SiteRelations;

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
	 * @var Languages
	 */
	private $languages;

	/**
	 * @var SiteRelations
	 */
	private $site_relation;

	/**
	 * Constructor
	 *
	 * @param SiteRelations $site_relation Site relations API object.
	 * @param Languages     $languages     Languages API object.
	 */
	public function __construct( SiteRelations $site_relation, Languages $languages ) {

		if ( ! is_network_admin() ) {
			return;
		}

		$this->site_relation = $site_relation;

		$this->languages = $languages;

		add_action( 'wpmu_new_blog', [ $this, 'update' ] );

		add_action( 'network_site_new_form', [ new Mlp_New_Site_View( $languages ), 'render' ] );
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

		if ( ! $posted )
			return;

		$languages = (array) get_site_option( 'inpsyde_multilingual', [] );

		if ( empty ( $languages[ $blog_id ] ) )
			$languages[ $blog_id ] = [];

		$languages[ $blog_id ][ 'lang' ] = str_replace( '-', '_', $posted );

		// Set alternative title
		if ( isset ( $_POST[ 'inpsyde_multilingual_text' ] ) )
			$languages[ $blog_id ][ 'text' ] = $_POST[ 'inpsyde_multilingual_text' ];

		update_site_option( 'inpsyde_multilingual', $languages );
	}

	/**
	 * Get language from post request.
	 *
	 * @return bool|string
	 */
	private function get_posted_language() {

		if ( ! isset ( $_POST[ 'inpsyde_multilingual_lang' ] ) )
			return FALSE;

		if ( '-1' === $_POST[ 'inpsyde_multilingual_lang' ] )
			return FALSE;

		return $_POST[ 'inpsyde_multilingual_lang' ];
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

		$available_language = $this->languages->get_languages( [
			'fields'     => 'wp_locale',
			'conditions' => [
				[
					'field' => 'http_name',
					'value' => $posted,
				],
			],
		] );

		// no results found? -> return
		if ( empty( $available_language ) )
			return;

		// getting the first wp_locale
		$wp_locale = $available_language[ 0 ]->wp_locale;

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

		if ( empty ( $_POST[ 'related_blogs' ] ) )
			return 0;

		$new_related = (array) $_POST[ 'related_blogs' ];
		$related     = array_map( 'intval', $new_related );

		return $this->site_relation->insert_relations( $blog_id, $related );
	}
}
