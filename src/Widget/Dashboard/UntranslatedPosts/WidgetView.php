<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Widget\Dashboard\View;

/**
 * Untranslated posts widget view.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
final class WidgetView implements View {

	/**
	 * @var PostRepository
	 */
	private $post_repository;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations  $site_relations  Site relations API object.
	 * @param PostRepository $post_repository Untranslated posts repository object.
	 */
	public function __construct( SiteRelations $site_relations, PostRepository $post_repository ) {

		$this->site_relations = $site_relations;

		$this->post_repository = $post_repository;
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
			return;
		}

		$have_untranslated_posts = false;
		?>
		<table class="widefat">
			<?php foreach ( $related_site_ids as $related_site_id ) : ?>
				<?php switch_to_blog( $related_site_id ); ?>
				<?php $untranslated_posts = $this->post_repository->get_untranslated_posts(); ?>
				<?php if ( $untranslated_posts ) : ?>
					<?php $have_untranslated_posts = true; ?>
					<tr>
						<th colspan="3">
							<strong>
								<?php
								/* translators: %s: site name */
								$message = __( 'Pending Translations for %s', 'multilingual-press' );
								printf( $message, get_bloginfo( 'name' ) );
								?>
							</strong>
						</th>
					</tr>
					<?php foreach ( array_column( $untranslated_posts, 'ID' ) as $post_id ) : ?>
						<tr>
							<td style="width: 20%;">
								<?php edit_post_link( __( 'Translate', 'multilingual-press' ), '', '', $post_id ); ?>
							</td>
							<td style="width: 55%;">
								<?php echo esc_html( get_the_title( $post_id ) ); ?>
							</td>
							<td style="width: 25%;">
								<?php echo esc_html( get_the_time( get_option( 'date_format' ), $post_id ) ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php restore_current_blog(); ?>
			<?php endforeach; ?>
			<?php if ( ! $have_untranslated_posts ) : ?>
				<tr>
					<td colspan="3">
						<?php esc_html_e( 'No untranslated posts found.', 'multilingual-press' ); ?>
					</td>
				</tr>
			<?php endif; ?>
		</table>
		<?php
	}
}
