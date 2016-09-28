<?php

use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Database\Table\ContentRelations;
use Inpsyde\MultilingualPress\Database\Table\Languages;
use Inpsyde\MultilingualPress\Database\Table\SiteRelations;
use Inpsyde\MultilingualPress\Database\WPDBTableInstaller;

/**
 * MultilingualPress Installation
 *
 * @version 2014.09.05
 * @author  Inpsyde GmbH, ChriCo, toscho
 * @license GPL
 */
class Mlp_Update_Plugin_Data {

	/**
	 * @var Mlp_Site_Relations_Interface
	 */
	private $site_relations;

	/**
	 * @var VersionNumber
	 */
	private $last_version;

	/**
	 * @var VersionNumber
	 */
	private $current_version;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var array
	 */
	private $all_sites;

	/**
	 * @todo Remove with WordPress 4.6 + 2.
	 * @var bool
	 */
	private $is_pre_4_6;

	/**
	 * Constructor
	 *
	 * @param   wpdb                            $wpdb
	 * @param   VersionNumber                   $current_version
	 * @param   VersionNumber                   $last_version
	 * @param Mlp_Site_Relations_Interface $site_relations
	 */
	public function __construct(
		wpdb                            $wpdb,
		VersionNumber    $current_version,
		VersionNumber    $last_version,
		Mlp_Site_Relations_Interface $site_relations
	) {

		$this->wpdb = $wpdb;

		$this->current_version = $current_version;

		$this->last_version = $last_version;

		$this->site_relations = $site_relations;

		// TODO: With WordPress 4.6 + 2, just use `get_sites()`, and remove `$this->is_pre_4_6`.
		// Get the unaltered WordPress version.
		require ABSPATH . WPINC . '/version.php';
		/** @var string $wp_version */
		$this->is_pre_4_6 = version_compare( $wp_version, '4.6-RC1', '<' );

		$this->all_sites = $this->is_pre_4_6 ? wp_get_sites() : get_sites();
	}

	/**
	 * Handles the update routines.
	 *
	 * @param Mlp_Network_Plugin_Deactivation_Interface $deactivator
	 * @return void
	 */
	public function update( Mlp_Network_Plugin_Deactivation_Interface $deactivator ) {

		$deactivator->deactivate( [
			'disable-acf.php',
			'mlp-wp-seo-compat.php'
		] );
		// add hook to import active languages when reset is done
		add_action( 'mlp_reset_table_done', [ $this, 'import_active_languages' ] );

		// The site option with the version number exists since 2.0. If the last
		// version is a fallback, it is a version below 2.0.
		if ( VersionNumber::FALLBACK_VERSION === $this->last_version )
			$this->update_plugin_data( 1 );
		else
			$this->update_plugin_data( $this->last_version );
	}

	/**
	 * Handle updates.
	 *
	 * @param string|int $last_version Last plugin version.
	 *
	 * @return bool
	 */
	private function update_plugin_data( $last_version ) {

		$table_prefix = $this->wpdb->base_prefix;

		if ( $last_version === 1 ) {
			$this->import_active_languages( new Languages( $table_prefix ) );
		}

		if ( version_compare( $last_version, '2.0.4', '<' ) ) {
			$installer = new WPDBTableInstaller();
			$installer->install( new SiteRelations( $table_prefix ) );

			$this->import_site_relations();
		}

		if ( version_compare( $last_version, '2.3.2', '<' ) ) {
			$this->update_type_column( new ContentRelations( $table_prefix ) );
		}

		// remove unneeded plugin data
		delete_option( 'inpsyde_companyname' );

		return update_site_option( 'mlp_version', $this->current_version );
	}

	/**
	 * Move site relationships from separate options to network table.
	 *
	 * @return void
	 */
	private function import_site_relations() {

		$option_name = 'inpsyde_multilingual_blog_relationship';
		$inserted = 0;

		foreach ( $this->all_sites as $site ) {
			// TODO: With WordPress 4.6 + 2, just use `$site->id`.
			$site_id = $this->is_pre_4_6 ? $site['blog_id'] : $site->id;

			$linked = get_blog_option( $site_id, $option_name, [] );
			if ( ! empty( $linked ) ) {
				$inserted += $this->site_relations->set_relation( $site_id, $linked );
			}

			delete_blog_option( $site_id, $option_name );
		}
	}

	/**
	 * Update mlp_multilingual_linked table and set type to "post" if empty
	 *
	 * @param Table $linked
	 * @return void
	 */
	private function update_type_column( Table $linked ) {

		$table = $linked->name();
		$this->wpdb->query(
			'UPDATE ' . $table . ' set ml_type = "post" WHERE ml_type NOT IN("post", "term")'
		);
	}

	/**
	 * Load the localization
	 *
	 * @since 0.1
	 * @uses load_plugin_textdomain, plugin_basename
	 * @param Table $languages
	 * @return void
	 */
	private function import_active_languages( Table $languages ) {

		// get active languages
		$mlp_settings = get_site_option( 'inpsyde_multilingual' );

		if ( empty ( $mlp_settings ) )
			return;

		$table = $languages->name();
		$sql   = 'SELECT ID FROM ' . $table . 'WHERE wp_locale = %s OR iso_639_1 = %s';

		foreach ( $mlp_settings as $mlp_site ) {
			$text    = $mlp_site[ 'text' ] != '' ? $mlp_site[ 'text' ] : $mlp_site[ 'lang' ];
			$query   = $this->wpdb->prepare( $sql, $mlp_site[ 'lang' ], $mlp_site[ 'lang' ] );
			$lang_id = $this->wpdb->get_var( $query );

			// language not found -> insert
			if ( empty ( $lang_id ) ) {
				// @todo add custom name
				$this->wpdb->insert( $table, [
					'english_name' => $text,
					'wp_locale'    => $mlp_site[ 'lang' ],
					'http_name'    => str_replace( '_', '-', $mlp_site[ 'lang' ] ),
				] );
			}
			// language found -> change priority
			else {
				$this->wpdb->update(
					$table,
					[ 'priority' => 10 ],
					[ 'ID'       => $lang_id ]
				);
			}
		}
	}

	/**
	 * Install plugin tables.
	 *
	 * @return bool
	 */
	public function install_plugin() {

		$table_prefix = $this->wpdb->base_prefix;

		// TODO: Inject (empty) installer in constructor.
		$installer = new WPDBTableInstaller();
		$installer->install( new Languages( $table_prefix ) );
		$installer->install( new ContentRelations( $table_prefix ) );
		$installer->install( new SiteRelations( $table_prefix ) );

		return update_site_option( 'mlp_version', $this->current_version );
	}
}
