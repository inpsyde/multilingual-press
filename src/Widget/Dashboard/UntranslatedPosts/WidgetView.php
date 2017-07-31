<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\NetworkState;
use Inpsyde\MultilingualPress\Widget\Dashboard\View;

/**
 * Untranslated posts widget view.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
final class WidgetView implements View {

	/**
	 * @var PostsRepository
	 */
	private $posts_repository;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations   $site_relations   Site relations API object.
	 * @param PostsRepository $posts_repository Untranslated posts repository object.
	 */
	public function __construct( SiteRelations $site_relations, PostsRepository $posts_repository ) {

		$this->site_relations = $site_relations;

		$this->posts_repository = $posts_repository;
	}

	/**
	 * Renders the widget's view.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $object   Queried object, or other stuff.
	 * @param array $instance Widget settings.
	 *
	 * @return void
	 */
	public function render( $object, array $instance ) {

		$related_site_ids = $this->site_relations->get_related_site_ids();
		if ( ! $related_site_ids ) {
			echo '<p>' . esc_html__( 'There are no sites related to this one.', 'multilingualpress' ) . '</p>';

			return;
		}

		$network_state = NetworkState::from_globals();

		ob_start();

		foreach ( $related_site_ids as $related_site_id ) {
			switch_to_blog( $related_site_id );
			$this->render_posts( $this->posts_repository->get_untranslated_posts() );
		}

		$network_state->restore();

		$rows = ob_get_clean();
		if ( ! $rows ) {
			echo '<p>' . esc_html__( 'No untranslated posts found.', 'multilingualpress' ) . '</p>';

			return;
		}

		echo '<table class="widefat">' . $rows . '</table>';
	}

	/**
	 * Renders the markup for the give posts.
	 *
	 * @param array $posts An array with untranslated posts.
	 *
	 * @return void
	 */
	private function render_posts( array $posts ) {

		if ( ! $posts ) {
			return;
		}
		?>
		<tr>
			<th colspan="3">
				<strong>
					<?php
					/* translators: %s: site name */
					$message = __( 'Pending Translations for %s', 'multilingualpress' );
					printf( $message, get_bloginfo( 'name' ) );
					?>
				</strong>
			</th>
		</tr>
		<?php foreach ( array_column( $posts, 'ID' ) as $post_id ) : ?>
			<tr>
				<td style="width: 20%;">
					<?php edit_post_link( __( 'Translate', 'multilingualpress' ), '', '', $post_id ); ?>
				</td>
				<td style="width: 55%;">
					<?php echo esc_html( get_the_title( $post_id ) ); ?>
				</td>
				<td style="width: 25%;">
					<?php echo esc_html( get_the_time( get_option( 'date_format' ), $post_id ) ); ?>
				</td>
			</tr>
		<?php endforeach;
	}
}
