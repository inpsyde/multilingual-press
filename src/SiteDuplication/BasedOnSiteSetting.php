<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Site duplication "Based on site" setting.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
final class BasedOnSiteSetting implements SiteSettingViewModel {

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * @var string
	 */
	private $id = 'mlp-base-site-id';

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param \wpdb $db    WordPress database object.
	 * @param Nonce $nonce Nonce object.
	 */
	public function __construct( \wpdb $db, Nonce $nonce ) {

		$this->db = $db;

		$this->nonce = $nonce;
	}

	/**
	 * Renders the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	public function render( int $site_id ) {

		?>
		<select id="<?php echo esc_attr( $this->id ); ?>"
			name="<?php echo esc_attr( SiteDuplicator::NAME_BASED_ON_SITE ); ?>" autocomplete="off">
			<?php $this->render_options(); ?>
		</select>
		<?php
		nonce_field( $this->nonce );
	}

	/**
	 * Returns the title of the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the site setting.
	 */
	public function title(): string {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Based on site', 'multilingualpress' ),
			esc_attr( $this->id )
		);
	}

	/**
	 * Renders the option tags.
	 *
	 * @return void
	 */
	private function render_options() {

		?>
		<option value="0"><?php esc_html_e( 'Choose site', 'multilingualpress' ); ?></option>
		<?php
		foreach ( $this->get_all_sites() as $site ) {
			?>
			<option value="<?php echo esc_attr( $site['id'] ); ?>">
				<?php echo esc_url( $site['domain'] . ( '/' === $site['path'] ? '' : $site['path'] ) ); ?>
			</option>
			<?php
		}
	}

	/**
	 * Returns all existing sites.
	 *
	 * @return string[][] An array with site data arrays.
	 */
	private function get_all_sites(): array {

		$query = "SELECT blog_id AS id, domain, path FROM {$this->db->blogs} WHERE deleted = 0 AND site_id = %s";
		$query = $this->db->prepare( $query, $this->db->siteid );

		return (array) $this->db->get_results( $query, ARRAY_A );
	}
}
