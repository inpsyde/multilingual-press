<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\SiteRelations;

// TODO: Go on refactoring...

/**
 * Dashboard widget for incomplete translations.
 */
class Mlp_Dashboard_Widget {

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param SiteRelations $site_relations
	 */
	public function __construct( SiteRelations $site_relations ) {

		$this->site_relations = $site_relations;
	}

	/**
	 * Wires up all functions.
	 *
	 * @return void
	 */
	public function initialize() {

		/**
		 * Filters the capability required to view the dashboard widget.
		 *
		 * @param string $capability Capability required to view the dashboard widget.
		 */
		$capability = apply_filters( 'mlp_dashboard_widget_access', 'edit_others_posts' );
		if ( current_user_can( $capability ) ) {
			add_action( 'wp_dashboard_setup', function () {

				wp_add_dashboard_widget(
					'multilingualpress-dashboard-widget',
					__( 'Untranslated Posts', 'multilingual-press' ),
					[ $this, 'dashboard_widget' ]
				);
			} );
		}
	}

	/**
	 * Displays the posts which are not translated yet.
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function dashboard_widget() {

		$related_blogs = $this->site_relations->get_related_site_ids();
		if ( ! $related_blogs ) {
			?>
			<p>
				<?php _e( 'Sorry, there are no connected sites in the system for this site.', 'multilingual-press' ); ?>
			</p>
			<?php
			return;
		}
		?>
		<table class="widefat">
			<?php foreach ( array_unique( $related_blogs ) as $blog_to_translate ) : ?>
				<?php switch_to_blog( $blog_to_translate ); ?>
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
				<?php
				// Post status 'any' automatically excludes both 'auto-draft' and 'trash'.
				// Not suppressing filters (which is done by default when using get_posts()) makes caching possible.
				$posts_to_translate = get_posts( [
					'suppress_filters' => false,
					'post_status'      => 'any',
					'meta_query'       => [
						'relation' => 'OR',
						[
							'key'     => '_post_is_translated',
							'compare' => '!=',
							'value'   => '1',
						],
						[
							'key'     => 'post_is_translated',
							'compare' => '!=',
							'value'   => '1',
						],
					],
				] );
				?>
				<?php if ( $posts_to_translate ) : ?>
					<?php foreach ( $posts_to_translate as $post ) : ?>
						<tr>
							<td style="width: 20%;">
								<?php edit_post_link( __( 'Translate', 'multilingual-press' ), '', '', $post->ID ); ?>
							</td>
							<td style="width: 55%;">
								<?php echo esc_html( get_the_title( $post->ID ) ); ?>
							</td>
							<td style="width: 25%;">
								<?php echo esc_html( get_the_time( get_option( 'date_format' ), $post->ID ) ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php restore_current_blog(); ?>
			<?php endforeach; ?>
		</table>
		<?php
	}
}
