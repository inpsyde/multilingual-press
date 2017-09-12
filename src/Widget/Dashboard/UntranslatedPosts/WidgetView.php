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
	 * @var WidgetConfigurator
	 */
	private $configurator;

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
	 * @param SiteRelations      $site_relations   Site relations API object.
	 * @param PostsRepository    $posts_repository Untranslated posts repository object.
	 * @param WidgetConfigurator $configurator     Widget configurator object.
	 */
	public function __construct(
		SiteRelations $site_relations,
		PostsRepository $posts_repository,
		WidgetConfigurator $configurator
	) {

		$this->site_relations = $site_relations;

		$this->posts_repository = $posts_repository;

		$this->configurator = $configurator;
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

		$this->configurator = $this->configurator->with_widget_id( (string) $instance['id'] );

		$network_state = NetworkState::create();

		$has_untranslated_posts = false;

		echo '<table class="widefat">';

		foreach ( $this->get_site_ids() as $site_id ) {
			switch_to_blog( $site_id );

			$untranslated_posts = $this->posts_repository->get_untranslated_posts();
			$untranslated_posts = array_filter( $untranslated_posts, function ( \WP_Post $post ) {

				return current_user_can( 'edit_post', $post );
			} );
			if ( $untranslated_posts ) {
				$has_untranslated_posts = true;

				$this->render_posts( $untranslated_posts );
			}
		}

		$network_state->restore();

		if ( ! $has_untranslated_posts ) {
			echo '<tr><td>' . esc_html__( 'No untranslated posts found.', 'multilingualpress' ) . '</td></tr>';
		}

		echo '</table>';
	}

	/**
	 * Returns the site IDs for the widget to display posts from.
	 *
	 * @return int[] Site IDs.
	 */
	private function get_site_ids(): array {

		$site_ids = [
			(int) get_current_blog_id(),
		];

		if ( $this->configurator->is_displaying_remote_sites() ) {
			$related_site_ids = $this->site_relations->get_related_site_ids();
			$related_site_ids = array_filter( $related_site_ids, function ( int $site_id ) {

				return is_user_member_of_blog( 0, $site_id );
			} );

			$site_ids = array_merge( $site_ids, $related_site_ids );
		}

		return $site_ids;
	}

	/**
	 * Renders the markup for the give posts.
	 *
	 * @param array $posts An array with untranslated posts.
	 *
	 * @return void
	 */
	private function render_posts( array $posts ) {

		/* translators: %s: site name */
		$message = __( 'Pending Translations for %s', 'multilingualpress' );
		$message = sprintf( $message, get_bloginfo( 'name' ) );
		?>
		<tr>
			<th colspan="2">
				<strong><?php echo esc_html( $message ); ?></strong>
			</th>
		</tr>
		<?php foreach ( array_column( $posts, 'ID' ) as $post_id ) : ?>
			<tr>
				<td style="width: 66%;">
					<?php $this->edit_post_link( (int) $post_id ); ?>
				</td>
				<td>
					<?php echo esc_html( get_the_time( get_option( 'date_format' ), $post_id ) ); ?>
				</td>
			</tr>
		<?php
		endforeach;
	}

	/**
	 * Renders the edit link for the post with the given ID.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	private function edit_post_link( int $post_id ) {

		$text = get_the_title( $post_id ) ?: __( 'Translate', 'multilingualpress' );
		edit_post_link( esc_html( $text ), '', '', $post_id );

		$post_type = get_post_type( $post_id );
		if ( ! $post_type || 'post' === $post_type ) {
			return;
		}

		$post_type = get_post_type_object( $post_type );
		if ( $post_type ) {
			echo esc_html( " &mdash; {$post_type->labels->singular_name}" );
		}
	}
}
