<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\SiteDuplication;

use wpdb;

/**
 * Site duplication settings user interface view.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
class SettingsView {

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param wpdb $db WordPress database object.
	 */
	public function __construct( wpdb $db ) {

		$this->db = $db;
	}

	/**
	 * Renders the settings user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render() {

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="mlp-base-site-id">
					<?php esc_html_e( 'Based on site', 'multilingual-press' ); ?>
				</label>
			</th>
			<td>
				<select id="mlp-base-site-id" name="blog[basedon]" autocomplete="off">
					<option value="0"><?php _e( 'Choose site', 'multilingual-press' ); ?></option>
					<?php foreach ( (array) $this->get_all_sites() as $site ) : ?>
						<option value="<?php echo esc_attr( $site['id'] ); ?>">
							<?php echo esc_url( $site['domain'] . ( '/' === $site['path'] ? '' : $site['path'] ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr class="form-field hide-if-js">
			<th scope="row">
				<?php esc_html_e( 'Plugins', 'multilingual-press' ); ?>
			</th>
			<td>
				<label for="mlp-activate-plugins">
					<input type="checkbox" value="1" id="mlp-activate-plugins" name="blog[activate_plugins]"
						checked="checked">
					<?php
					esc_html_e( 'Activate all plugins that are active on the source site', 'multilingual-press' );
					?>
				</label>
			</td>
		</tr>
		<?php
		/**
		 * Filters the default search engine visibility value when adding a new site.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $visible Whether or not the new site should be visible by default.
		 */
		$visible = (bool) apply_filters( 'multilingualpress.new_site_search_engine_visibility', false );
		?>
		<tr class="form-field">
			<th scope="row">
				<?php esc_html_e( 'Search Engine Visibility', 'multilingual-press' ); ?>
			</th>
			<td>
				<label for="inpsyde_multilingual_visibility">
					<input type="checkbox" value="0" id="inpsyde_multilingual_visibility" name="blog[visibility]"
						<?php checked( ! $visible ); ?>>
					<?php esc_html_e( 'Discourage search engines from indexing this site', 'multilingual-press' ); ?>
				</label>

				<p class="description">
					<?php esc_html_e( 'It is up to search engines to honor this request.', 'multilingual-press' ); ?>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Returns all existing sites.
	 *
	 * @return string[][] An array with site data arrays.
	 */
	private function get_all_sites() {

		$query = "SELECT blog_id AS id, domain, path FROM {$this->db->blogs} WHERE deleted = 0 AND site_id = %s";
		$query = $this->db->prepare( $query, $this->db->siteid );

		return $this->db->get_results( $query, ARRAY_A );
	}
}
