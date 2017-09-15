<?php
/**
 * MultilingualPress Installation
 *
 * @version 2014.09.05
 * @author  Inpsyde GmbH, ChriCo, toscho
 * @license GPL
 */
class Mlp_Update_Plugin_Data {

	/**
	 * Plugin data
	 *
	 * @var Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * @var Mlp_Version_Number_Interface
	 */
	private $last_version;

	/**
	 * @var Mlp_Version_Number_Interface
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
	 * @param   Inpsyde_Property_List_Interface $plugin_data
	 * @param   wpdb                            $wpdb
	 * @param   Mlp_Version_Number_Interface    $current_version
	 * @param   Mlp_Version_Number_Interface    $last_version
	 */
	public function __construct(
		Inpsyde_Property_List_Interface $plugin_data,
		wpdb $wpdb,
		Mlp_Version_Number_Interface $current_version,
		Mlp_Version_Number_Interface $last_version
	) {

		$this->plugin_data     = $plugin_data;
		$this->last_version    = $last_version;
		$this->current_version = $current_version;
		$this->wpdb            = $wpdb;

		// TODO: With WordPress 4.6 + 2, just use `get_sites()`, and remove `$this->is_pre_4_6`.
		// Get the unaltered WordPress version.
		require ABSPATH . WPINC . '/version.php';
		/** @var string $wp_version */
		$this->is_pre_4_6 = version_compare( $wp_version, '4.6-RC1', '<' );

		// @codingStandardsIgnoreLine as the deprecated function is only used in old versions of WordPress.
		$this->all_sites = $this->is_pre_4_6 ? wp_get_sites() : get_sites();
	}

	/**
	 * Handles the update routines.
	 *
	 * @param Mlp_Network_Plugin_Deactivation_Interface $deactivator
	 * @return void
	 */
	public function update( Mlp_Network_Plugin_Deactivation_Interface $deactivator ) {

		$deactivator->deactivate(
			array(
				'disable-acf.php',
				'mlp-wp-seo-compat.php',
			)
		);
		// add hook to import active languages when reset is done
		add_action( 'mlp_reset_table_done', array( $this, 'import_active_languages' ) );

		// The site option with the version number exists since 2.0. If the last
		// version is a fallback, it is a version below 2.0.
		if ( Mlp_Version_Number_Interface::FALLBACK_VERSION === $this->last_version ) {
			$this->update_plugin_data( 1 );
		} else {
			$this->update_plugin_data( $this->last_version );
		}
	}

	/**
	 * Handle updates.
	 *
	 * @param string|int $last_version Last plugin version.
	 *
	 * @return bool
	 */
	private function update_plugin_data( $last_version ) {

		if ( 1 === $last_version ) {
			$this->import_active_languages( new Mlp_Db_Languages_Schema( $this->wpdb ) );
		}

		if ( version_compare( $last_version, '2.0.4', '<' ) ) {
			$installer = new Mlp_Db_Installer( new Mlp_Site_Relations_Schema( $this->wpdb ) );
			$installer->install();
			$this->import_site_relations();
		}

		if ( version_compare( $last_version, '2.3.2', '<' ) ) {
			$this->update_type_column( new Mlp_Content_Relations_Schema( $this->wpdb ) );
		}

		// remove unneeded plugin data
		delete_option( 'inpsyde_companyname' );

		return update_site_option( 'mlp_version', $this->plugin_data->get( 'version' ) );
	}

	/**
	 * Move site relationships from separate options to network table.
	 *
	 * @return void
	 */
	private function import_site_relations() {

		/** @var Mlp_Site_Relations_Interface $db */
		$relations = $this->plugin_data->get( 'site_relations' );
		$option_name = 'inpsyde_multilingual_blog_relationship';
		$inserted = 0;

		foreach ( $this->all_sites as $site ) {
			// TODO: With WordPress 4.6 + 2, just use `$site->id`.
			$site_id = $this->is_pre_4_6 ? $site['blog_id'] : $site->id;

			$linked = get_blog_option( $site_id, $option_name, array() );
			if ( ! empty( $linked ) ) {
				$inserted += $relations->set_relation( $site_id, $linked );
			}

			delete_blog_option( $site_id, $option_name );
		}
	}

	/**
	 * Update mlp_multilingual_linked table and set type to "post" if empty
	 *
	 * @param Mlp_Db_Schema_Interface $linked
	 * @return void
	 */
	private function update_type_column( Mlp_Db_Schema_Interface $linked ) {

		$table = $linked->get_table_name();
		$this->wpdb->query(
			'UPDATE ' . $table . ' set ml_type = "post" WHERE ml_type NOT IN("post", "term")'
		);
	}

	/**
	 * Load the localization
	 *
	 * @since 0.1
	 * @uses load_plugin_textdomain, plugin_basename
	 * @param Mlp_Db_Schema_Interface $languages
	 * @return void
	 */
	private function import_active_languages( Mlp_Db_Schema_Interface $languages ) {

		// get active languages
		$mlp_settings = get_site_option( 'inpsyde_multilingual' );

		if ( empty( $mlp_settings ) ) {
			return;
		}

		$table = $languages->get_table_name();
		$sql   = 'SELECT ID FROM ' . $table . 'WHERE wp_locale = %s OR iso_639_1 = %s';

		foreach ( $mlp_settings as $mlp_site ) {
			$text    = '' !== $mlp_site['text'] ? $mlp_site['text'] : $mlp_site['lang'];
			$query   = $this->wpdb->prepare( $sql, $mlp_site['lang'], $mlp_site['lang'] );
			$lang_id = $this->wpdb->get_var( $query );

			// language not found -> insert
			if ( empty( $lang_id ) ) {
				// @todo add custom name
				$this->wpdb->insert(
					$table,
					array(
						'english_name' => $text,
						'wp_locale'    => $mlp_site['lang'],
						'http_name'    => str_replace( '_', '-', $mlp_site['lang'] ),
					)
				);
			} // language found -> change priority
			else {
				$this->wpdb->update(
					$table,
					array(
						'priority' => 10,
					),
					array(
						'ID' => $lang_id,
					)
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

		$installer = new Mlp_Db_Installer( new Mlp_Db_Languages_Schema( $this->wpdb ) );
		$installer->install();
		$installer->install( new Mlp_Content_Relations_Schema( $this->wpdb ) );
		$installer->install( new Mlp_Site_Relations_Schema( $this->wpdb ) );

		return update_site_option( 'mlp_version', $this->plugin_data->get( 'version' ) );
	}

}
