<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\NetworkState;
use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

use function Inpsyde\MultilingualPress\get_site_language;

/**
 * MultilingualPress "Relationships" site setting.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class RelationshipsSiteSetting implements SiteSettingViewModel {

	/**
	 * @var string
	 */
	private $id = 'mlp-site-relations';

	/**
	 * @var SiteSettingsRepository
	 */
	private $repository;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingsRepository $repository     Site settings repository object.
	 * @param SiteRelations          $site_relations Site relations API object.
	 */
	public function __construct( SiteSettingsRepository $repository, SiteRelations $site_relations ) {

		$this->repository = $repository;

		$this->site_relations = $site_relations;
	}

	/**
	 * Returns the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string The markup for the site setting.
	 */
	public function markup( int $site_id ): string {

		ob_start();
		?>
		<p class="description">
			<?php
			esc_html_e(
				'You can connect this site only to sites with an assigned language. Other sites will not show up here.',
				'multilingualpress'
			);
			?>
		</p>
		<?php

		return $this->get_relationships( $site_id ) . ob_get_clean();
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
			esc_html__( 'Relationships', 'multilingualpress' ),
			esc_attr( $this->id )
		);
	}

	/**
	 * Returns the markup for all relationships.
	 *
	 * @param int $base_site_id Current site ID.
	 *
	 * @return string The markup for all relationships.
	 */
	private function get_relationships( int $base_site_id ): string {

		$site_ids = $this->repository->get_site_ids( [ $base_site_id ] );
		if ( ! $site_ids ) {
			return '';
		}

		$network_state = NetworkState::create();

		$relationships = array_reduce( $site_ids, function ( $relationships, $site_id ) use ( $base_site_id ) {

			switch_to_blog( $site_id );

			$site_name = get_bloginfo( 'name' );

			$related_site_ids = $this->site_relations->get_related_site_ids( (int) $site_id );

			$id = "{$this->id}-{$site_id}";

			// translators: 1 = site name, 2 = site language.
			$message = _x( '%1$s - %2$s', 'Site relationships', 'multilingualpress' );
			$message = sprintf(
				$message,
				$site_name,
				get_site_language( $site_id, false )
			);

			ob_start();
			?>
			<p>
				<label for="<?php echo esc_attr( $id ); ?>">
					<input type="checkbox"
						name="<?php echo esc_attr( SiteSettingsRepository::NAME_RELATIONSHIPS ); ?>[]"
						value="<?php echo esc_attr( $site_id ); ?>" id="<?php echo esc_attr( $id ); ?>"
						<?php checked( in_array( $base_site_id, $related_site_ids, true ) ); ?>>
					<?php echo esc_html( $message ); ?>
				</label>
			</p>
			<?php
			return $relationships . ob_get_clean();
		}, '' );

		$network_state->restore();

		return $relationships;
	}
}
