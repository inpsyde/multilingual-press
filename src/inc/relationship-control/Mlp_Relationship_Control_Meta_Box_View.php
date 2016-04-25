<?php

/**
 * Class Mlp_Relationship_Control_Meta_Box_View
 *
 * Show control elements for relationship control feature.
 *
 * @version 2014.03.14
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Relationship_Control_Meta_Box_View {

	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * @var int
	 */
	private $remote_site_id;

	/**
	 * @var int
	 */
	private $remote_post_id = 0;

	/**
	 * @var Mlp_Relationship_Control_Data
	 */
	private $data;

	/**
	 * @var Mlp_Updatable
	 */
	private $updater;

	/**
	 * @param Mlp_Relationship_Control_Data $data
	 * @param Mlp_Updatable                 $updater
	 */
	public function __construct(
		Mlp_Relationship_Control_Data $data,
		Mlp_Updatable                 $updater
	) {

		$this->post            = $data->get_source_post();
		$this->remote_site_id  = $data->get_remote_site_id();
		$this->remote_post_id  = $data->get_remote_post_id();
		$this->site_id         = get_current_blog_id();
		$this->data            = $data;
		$this->updater         = $updater;
		$this->search_input_id = "mlp_post_search_$this->remote_site_id";
	}

	public function render() {

		$this->localize_script();

		$action_selector_id = "mlp_rsc_action_container_$this->remote_site_id";
		$search_selector_id = "mlp_rsc_search_container_$this->remote_site_id";
		?>
		<div class="mlp-relationship-control-box"
			 style="margin: .5em 0 .5em auto ">
			<?php
			printf(
				'<button type="button" class="button secondary mlp-rsc-button mlp-click-toggler" name="mlp_rsc_%2$d"
					data-toggle-target="#%3$s" data-search_box_id="%4$s">%1$s</button>',
				esc_html__( 'Change relationship', 'multilingual-press' ),
				$this->remote_site_id,
				$action_selector_id,
				$search_selector_id
			);
			?>
			<div id="<?php echo esc_attr( $action_selector_id ); ?>" class='hidden'>
				<div class="mlp-rc-settings">
					<div class="mlp-rc-actions" style="float: left; width: 20em;">
						<?php
						$actions = array (
							'stay' => esc_html__( 'Leave as is', 'multilingual-press' ),
							'new'  => esc_html__( 'Create new post', 'multilingual-press' ),
						);

						if ( $this->remote_post_id )
							$actions[ 'disconnect' ] = esc_html__( 'Remove relationship', 'multilingual-press' );

						foreach ( $actions as $key => $label )
							print '<p>'
								. $this->get_radio(
									   $key,
										   $label,
										   'stay',
										   'mlp-rc-action[' . $this->remote_site_id . ']',
										   'mlp-rc-input-id-' . $this->remote_site_id
								)
								. '</p>';

					?>
					<p>
						<label for="mlp-rc-input-id-<?php echo esc_attr( $this->remote_site_id ); ?>-search">
							<input
								type="radio"
								name="mlp-rc-action[<?php echo esc_attr( $this->remote_site_id ); ?>]"
								value="search"
								class="mlp-state-toggler"
								id="mlp-rc-input-id-<?php echo esc_attr( $this->remote_site_id ); ?>-search"
								data-toggle-target="#<?php echo esc_attr( $search_selector_id ); ?>">
							<?php esc_html_e( 'Select existing post &hellip;', 'multilingual-press' ) ?>
						</label>
					</p>
				</div>

					<div id="<?php echo esc_attr( $search_selector_id ); ?>"
						 style="display:none;float:left;max-width:30em">

						<label for="<?php echo esc_attr( $this->search_input_id ); ?>">
							<?php
							esc_html_e( 'Live search', 'multilingual-press' );
							?>
						</label>
						<?php
						print $this->get_search_input( $this->search_input_id );
						?>

						<ul class="mlp-search-results"
							id="mlp-search-results-<?php echo esc_attr( $this->remote_site_id ); ?>">
							<?php
							$this->updater->update( 'default.remote.posts' );
							?>
						</ul>
					</div>
				</div>
				<p>
					<?php
					$data_attrs = $this->add_id_values( '' );
					?>
					<input type="button"
						   class="button button-primary mlp-save-relationship-button"
						   value="<?php
						   esc_attr_e( 'Save and reload this page', 'multilingual-press' );
						   ?>" <?php print $data_attrs; ?>>
					<span class="description"><?php
						esc_html_e( 'Please save other changes first separately.', 'multilingual-press' );
						?></span>
				</p>
			</div>
		</div>
	<?php
	}

	/**
	 * Makes the relationships control settings available for JavaScript.
	 *
	 * @return void
	 */
	private function localize_script() {

		wp_localize_script( 'mlp-admin', 'mlpRelationshipControlSettings', array(
			'L10n' => array(
				'noPostSelected'       => __( 'Please select a post.', 'multilingual-press' ),
				'unsavedRelationships' => __(
					'You have unsaved changes in your post relationships. The changes you made will be lost if you navigate away from this page.',
					'multilingual-press'
				),
			),
		) );

		/**
		 * Filters the minimum number of characters required to fire the remote post search.
		 *
		 * @param int $threshold Minimum number of characters required to fire the remote post search.
		 */
		$threshold = (int) apply_filters( 'mlp_remote_post_search_threshold', 3 );
		$threshold = max( 1, $threshold );

		wp_localize_script( 'mlp-admin', 'mlpRemotePostSearchSettings', array(
			'threshold' => $threshold,
		) );
	}

	/**
	 * @param string $id
	 * @return string
	 */
	private function get_search_input( $id ) {

		$input = '<input type="search" class="mlp-search-field" id="' . $id . '"';
		$input = $this->add_id_values( $input );

		return $input . '>';
	}

	/**
	 * Add data attributes to a string.
	 *
	 * @param $str
	 * @return string
	 */
	private function add_id_values( $str ) {

		$data = array (
			'results-container-id' => "mlp-search-results-{$this->remote_site_id}",
			'source-site-id'       => $this->site_id,
			'source-post-id'       => $this->post->ID,
			'remote-site-id'       => $this->remote_site_id,
			'remote-post-id'       => $this->remote_post_id,
		);

		foreach ( $data as $key => $value )
			$str .= " data-$key='$value'";

		return $str;
	}

	/**
	 * @param string $key
	 * @param string $label
	 * @param string $selected
	 * @param string $name
	 * @param string $id_base
	 * @return string
	 */
	private function get_radio( $key, $label, $selected, $name, $id_base ) {

		return sprintf(
			'<label for="%5$s-%1$s">
				<input type="radio" name="%4$s" id="%5$s-%1$s" value="%1$s"%3$s>
				%2$s
			</label>',
			$key,
			$label,
			selected( $name, $selected, FALSE ),
			$name,
			$id_base
		);
	}
}
