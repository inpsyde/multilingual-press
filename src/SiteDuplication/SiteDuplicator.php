<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Database\TableDuplicator;
use Inpsyde\MultilingualPress\Database\TableList;
use Inpsyde\MultilingualPress\Database\TableReplacer;
use wpdb;

/**
 * Handles duplication of a site.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
class SiteDuplicator {

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_ACTIVATE_PLUGINS = 'activate_plugins';

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_BASED_ON_SITE = 'basedon';

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_SEARCH_ENGINE_VISIBILITY = 'visibility';

	/**
	 * @var ActivePlugins
	 */
	private $active_plugins;

	/**
	 * @var AttachmentCopier
	 */
	private $attachment_copier;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * @var TableDuplicator
	 */
	private $table_duplicator;

	/**
	 * @var TableList
	 */
	private $table_list;

	/**
	 * @var TableReplacer
	 */
	private $table_replacer;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param wpdb             $db                WordPress database object.
	 * @param TableList        $table_list        Table list object.
	 * @param TableDuplicator  $table_duplicator  Table duplicator object.
	 * @param TableReplacer    $table_replacer    Table replacer object.
	 * @param ActivePlugins    $active_plugins    Active plugin access object.
	 * @param ContentRelations $content_relations Content relations APU object.
	 * @param AttachmentCopier $attachment_copier Attachment copier object.
	 */
	public function __construct(
		wpdb $db,
		TableList $table_list,
		TableDuplicator $table_duplicator,
		TableReplacer $table_replacer,
		ActivePlugins $active_plugins,
		ContentRelations $content_relations,
		AttachmentCopier $attachment_copier
	) {

		$this->db = $db;

		$this->table_list = $table_list;

		$this->table_duplicator = $table_duplicator;

		$this->table_replacer = $table_replacer;

		$this->active_plugins = $active_plugins;

		$this->content_relations = $content_relations;

		$this->attachment_copier = $attachment_copier;
	}

	/**
	 * Duplicates a complete site to the new site just created.
	 *
	 * @since 3.0.0
	 *
	 * @param int $new_site_id The ID of the new site.
	 *
	 * @return bool Whether or not a site was duplicated successfully.
	 */
	public function duplicate_site( $new_site_id ) {

		if (
			empty( $_POST['blog'][ static::NAME_BASED_ON_SITE ] )
			|| 1 > $_POST['blog'][ static::NAME_BASED_ON_SITE ]
		) {
			return false;
		}

		$source_site_id = (int) $_POST['blog'][ static::NAME_BASED_ON_SITE ];

		// Switch to the source site.
		switch_to_blog( $source_site_id );

		$table_prefix = $this->db->prefix;

		$mapped_domain = $this->get_mapped_domain();

		// Switch to the new site.
		switch_to_blog( $new_site_id );

		$admin_email = get_option( 'admin_email' );

		$siteurl = get_option( 'siteurl' );

		// Important: FIRST, duplicate the tables, and THEN overwrite things. ;)
		$this->duplicate_tables( $source_site_id, $table_prefix );

		$this->set_urls( $siteurl, $mapped_domain );

		$this->set_admin_email( $admin_email );

		update_option( 'blogname', stripslashes( $_POST['blog']['title'] ) );

		$this->rename_user_roles_option( $table_prefix );

		// Set the search engine visibility.
		if ( isset( $_POST['blog'][ static::NAME_SEARCH_ENGINE_VISIBILITY ] ) ) {
			update_option( 'blog_public', (bool) $_POST['blog'][ static::NAME_SEARCH_ENGINE_VISIBILITY ] );
		}

		$this->handle_plugins();

		$this->handle_theme();

		$this->handle_content_relations( $source_site_id, $new_site_id );

		$this->attachment_copier->copy_attachments( $source_site_id );

		// Switch all the way back to the original site.
		restore_current_blog();
		restore_current_blog();

		/**
		 * Fires after successful site duplication.
		 *
		 * @since 3.0.0
		 *
		 * @param int $source_site_id The ID of the source site.
		 * @param int $new_site_id    The ID of the new site.
		 */
		do_action( 'multilingualpress.duplicated_site', $source_site_id, $new_site_id );

		return true;
	}

	/**
	 * Returns the primary domain if domain mapping is active.
	 *
	 * @return string The primary domain if domain mapping is active, and an empty string if not.
	 */
	private function get_mapped_domain() {

		if ( empty( $this->db->dmtable ) ) {
			return '';
		}

		$query = $this->db->prepare(
			"SELECT domain FROM {$this->db->dmtable} WHERE active = 1 AND blog_id = %s LIMIT 1",
			get_current_blog_id()
		);

		$domain = $this->db->get_var( $query );
		if ( ! $domain ) {
			return '';
		}

		return ( is_ssl() ? 'https://' : 'http://' ) . $domain;
	}

	/**
	 * Duplicates the tables of the given source site to the current site.
	 *
	 * @param int    $source_site_id The ID of the source site.
	 * @param string $table_prefix   The table prefix that is to be replaced.
	 *
	 * @return void
	 */
	private function duplicate_tables( $source_site_id, $table_prefix ) {

		$tables = $this->table_list->site_tables( $source_site_id );
		/**
		 * Filters the tables to duplicate from the source site for the current site.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $tables         The names of the tables to duplicate.
		 * @param int      $source_site_id The ID of the source site.
		 */
		$tables = (array) apply_filters( 'multilingualpress.duplicate_site_tables', $tables, $source_site_id );

		array_walk( $tables, function ( $table ) use ( $table_prefix ) {

			$new_table = preg_replace( "~^{$table_prefix}~", $this->db->prefix, $table );

			if ( $this->table_duplicator->duplicate_table( $table, $new_table ) ) {
				$this->table_replacer->replace_table( $new_table, $table );
			}
		} );
	}

	/**
	 * Sets the admin email address option to the given value.
	 *
	 * @param string $url    URL of the new site.
	 * @param string $domain Potential domain of the new site, if using domain mapping.
	 *
	 * @return void
	 */
	private function set_urls( $url, $domain ) {

		update_option( 'home', $url );

		/**
		 * Updating the siteurl option will start the URL renaming plugin.
		 * So, yes, the following code is correct, even though siteurl will get updated two times!
		 */
		if ( $domain ) {
			update_option( 'siteurl', $domain );
		}
		update_option( 'siteurl', $url );
	}

	/**
	 * Sets the admin email address option to the given value.
	 *
	 * Using update_option() would trigger a confirmation email to the new address, so we directly manipulate the db.
	 *
	 * @param string $admin_email New admin email address.
	 *
	 * @return void
	 */
	private function set_admin_email( $admin_email ) {

		$this->db->update(
			$this->db->options,
			[ 'option_value' => $admin_email ],
			[ 'option_name' => 'admin_email' ]
		);
	}

	/**
	 * Renames the user roles option according to the given table prefix.
	 *
	 * @param string $table_prefix New table prefix.
	 *
	 * @return void
	 */
	private function rename_user_roles_option( $table_prefix ) {

		$this->db->update(
			$this->db->options,
			[ 'option_name' => "{$this->db->prefix}user_roles" ],
			[ 'option_name' => "{$table_prefix}user_roles" ]
		);
	}

	/**
	 * Adapts all active plugins according to the setting included in the request.
	 *
	 * @return void
	 */
	private function handle_plugins() {

		if ( isset( $_POST['blog'][ static::NAME_ACTIVATE_PLUGINS ] ) ) {
			$this->active_plugins->activate();
		} else {
			$this->active_plugins->deactivate();
		}
	}

	/**
	 * Triggers potential setup routines of the used theme.
	 *
	 * @return void
	 */
	private function handle_theme() {

		$theme = wp_get_theme();
		/** This action is documented in wp-includes/theme.php. */
		do_action( 'switch_theme', $theme->get( 'Name' ), $theme, $theme );
	}

	/**
	 * Sets up content relations between the source site and the new site.
	 *
	 * @param int $source_site_id      The ID of the source site.
	 * @param int $destination_site_id The ID of the new site.
	 *
	 * @return void
	 */
	private function handle_content_relations( $source_site_id, $destination_site_id ) {

		if ( $this->content_relations->has_site_relations( $source_site_id ) ) {
			$this->content_relations->duplicate_relations( $source_site_id, $destination_site_id );

			return;
		}

		$this->content_relations->relate_all_posts( $source_site_id, $destination_site_id );

		$this->content_relations->relate_all_terms( $source_site_id, $destination_site_id );
	}
}
