<?php # -*- coding: utf-8 -*-

/**
 * Dashboard widget for incomplete translations.
 */
class Mlp_Dashboard_Widget {

	/**
	 * @var Inpsyde_Nonce_Validator
	 */
	private $nonce_validator;

	/**
	 * @var Mlp_Site_Relations_Interface
	 */
	private $site_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param Mlp_Site_Relations_Interface $site_relations
	 */
	public function __construct( Mlp_Site_Relations_Interface $site_relations ) {

		$this->site_relations = $site_relations;

		$this->nonce_validator = Mlp_Nonce_Validator_Factory::create( 'save_translation_completed_setting' );
	}

	/**
	 * Wires up all functions.
	 *
	 * @return void
	 */
	public function initialize() {

		// Register Translated post meta to the submit box.
		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );

		/**
		 * Filters the capability required to view the dashboard widget.
		 *
		 * @param string $capability Capability required to view the dashboard widget.
		 */
		$capability = apply_filters( 'mlp_dashboard_widget_access', 'edit_others_posts' );
		if ( current_user_can( $capability ) ) {
			// Register the dashboard widget.
			add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
		}

		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Displays the checkbox for the post meta.
	 *
	 * @since   0.1
	 *
	 * @wp-hook post_submitbox_misc_actions
	 *
	 * @return void
	 */
	public function post_submitbox_misc_actions() {

		$post_id = $this->get_post_id();

		$is_translated = $this->is_translated( $post_id );

		/**
		 * Filters the visibility of the 'Translation completed' checkbox.
		 *
		 * @param bool  $show_checkbox Show the checkbox?
		 * @param array $context       Post context. {
		 *                             'post_id'       => int
		 *                             'is_translated' => bool
		 *                             }
		 */
		$show_checkbox = (bool) apply_filters( 'mlp_show_translation_completed_checkbox', true, array(
			'post_id'       => $post_id,
			'is_translated' => $is_translated,
		) );
		if ( ! $show_checkbox ) {
			return;
		}
		?>
		<div class="misc-pub-section">
			<?php wp_nonce_field( $this->nonce_validator->get_action(), $this->nonce_validator->get_name() ); ?>
			<input type="hidden" name="post_is_translated_blogid"
				value="<?php echo get_current_blog_id(); ?>">
			<label for="post_is_translated">
				<input type="checkbox" name="post_is_translated" value="1" id="post_is_translated"
					<?php checked( $is_translated ); ?>>
				<?php esc_html_e( 'Translation completed', 'multilingual-press' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Registers the dashboard widget.
	 *
	 * @since   0.1
	 *
	 * @wp-hook wp_dashboard_setup
	 *
	 * @return void
	 */
	public function wp_dashboard_setup() {

		wp_add_dashboard_widget(
			'multilingualpress-dashboard-widget',
			__( 'Untranslated Posts', 'multilingual-press' ),
			array( $this, 'dashboard_widget' )
		);
	}

	/**
	 * Displays the posts which are not translated yet.
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function dashboard_widget() {

		$related_blogs = $this->site_relations->get_related_sites();
		if ( ! $related_blogs ) {
			?>
			<p>
				<?php
				esc_html_e( 'Sorry, there are no connected sites in the system for this site.', 'multilingual-press' );
				?>
			</p>
			<?php
			return;
		}

		/* translators: %s: site name */
		$message = __( 'Pending Translations for %s', 'multilingual-press' );
		?>
		<table class="widefat">
			<?php foreach ( array_unique( $related_blogs ) as $blog_to_translate ) : ?>
				<?php switch_to_blog( $blog_to_translate ); ?>
				<tr>
					<th colspan="3">
						<strong><?php echo esc_html( sprintf( $message, get_bloginfo( 'name' ) ) ); ?></strong>
					</th>
				</tr>
				<?php
				// Post status 'any' automatically excludes both 'auto-draft' and 'trash'.
				// Not suppressing filters (which is done by default when using get_posts()) makes caching possible.
				$posts_to_translate = get_posts( array(
					'suppress_filters' => false,
					'post_status'      => 'any',
					'meta_query'       => array(
						'relation' => 'OR',
						array(
							'key'   => '_post_is_translated',
							'value' => '0',
						),
						array(
							'key'   => 'post_is_translated',
							'value' => '0',
						),
					),
				) );
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

	/**
	 * Updates the post meta.
	 *
	 * @wp-hook save_post
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function save_post( $post_id ) {

		// If checkbox is not checked, return.
		if ( ! isset( $_POST['post_is_translated'] ) ) {
			return;
		}

		if ( ! $this->nonce_validator->is_valid() ) {
			return;
		}

		// We're only interested in published posts at this time.
		$post_status = get_post_status( $post_id );
		if ( ! in_array( $post_status, array( 'publish', 'draft' ), true ) ) {
			return;
		}

		// Check the current blog ID against the according hidden variable to avoid recursion.
		if (
			! isset( $_POST['post_is_translated_blogid'] )
			|| get_current_blog_id() !== (int) $_POST['post_is_translated_blogid']
		) {
			return;
		}

		delete_post_meta( $post_id, 'post_is_translated' );

		// Well, is this post translated? We just need the single way.
		$post_is_translated = ! empty( $_POST['post_is_translated'] ) && '1' === $_POST['post_is_translated'];
		update_post_meta( $post_id, '_post_is_translated', $post_is_translated );
	}

	/**
	 * Returns the current post ID, or 0 on failure.
	 *
	 * @return int
	 */
	private function get_post_id() {

		return absint( filter_input( INPUT_GET, 'post' ) );
	}

	/**
	 * Checks if the current post is translated already.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool
	 */
	private function is_translated( $post_id ) {

		if ( ! $post_id ) {
			return false;
		}

		if ( get_post_meta( $this->get_post_id(), '_post_is_translated', true ) ) {
			return true;
		}

		// old key
		if ( get_post_meta( $post_id, 'post_is_translated', true ) ) {
			return true;
		}

		return false;
	}
}
