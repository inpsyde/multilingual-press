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
	private $blog_id;

	/**
	 * @var int
	 */
	private $remote_blog_id;

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
		$this->remote_blog_id  = $data->get_remote_blog_id();
		$this->remote_post_id  = $data->get_remote_post_id();
		$this->blog_id         = get_current_blog_id();
		$this->data            = $data;
		$this->updater         = $updater;
		$this->search_input_id = "mlp_post_search_$this->remote_blog_id";

		add_action(
			"admin_footer-" . $GLOBALS[ 'hook_suffix' ],
			array ( $this, 'print_jquery' )
		);
	}

	public function render() {

		$action_selector_id = "mlp_rsc_action_container_$this->remote_blog_id";
		$search_selector_id = "mlp_rsc_search_container_$this->remote_blog_id";
		?>
		<div class="mlp-relationship-control-box"
			 style="margin: .5em 0 .5em auto ">
			<?php
			submit_button(
				esc_attr__( 'Change relationship', 'multilingualpress' ),
				'secondary mlp-rsc-button mlp_toggler',
				"mlp_rsc_{$this->remote_blog_id}", // unique name
				FALSE,
				array (
					'data-toggle_selector' => "#$action_selector_id",
					'data-search_box_id'   => $search_selector_id
				)
			);
			?>
			<div id="<?php print $action_selector_id; ?>" class='hidden'>
				<div class="mlp_rsc_action_list" style="float:left;width:20em;">
					<?php

					$actions = array (
						'stay' => esc_html__( 'Leave as is', 'multilingualpress' ),
						'new'  => esc_html__( 'Create new post', 'multilingualpress' ),
					);

					if ( $this->remote_post_id )
						$actions[ 'disconnect' ] = esc_html__( 'Remove relationship', 'multilingualpress' );

					foreach ( $actions as $key => $label )
						print '<p>'
							. $this->get_radio(
								   $key,
									   $label,
									   'stay',
									   'mlp_rsc_action[' . $this->remote_blog_id . ']',
									   'mlp_rsc_input_id_' . $this->remote_blog_id
							)
							. '</p>';

					?>
					<p>
						<label
							for="mlp_rsc_input_id_<?php print $this->remote_blog_id; ?>_search"
							class="mlp_toggler"
							data-toggle_selector="#<?php print $search_selector_id; ?>"
							>
							<input
								type="radio"
								name="mlp_rsc_action[<?php print $this->remote_blog_id; ?>]"
								value="search"
								id="mlp_rsc_input_id_<?php print $this->remote_blog_id; ?>_search"
								>
							<?php
							esc_html_e( 'Select existing post &hellip;', 'multilingualpress' )
							?>
						</label>
					</p>
				</div>

				<div id="<?php print $search_selector_id; ?>"
					 style="display:none;float:left;max-width:30em">

					<label for="<?php print $this->search_input_id; ?>">
						<?php
						esc_html_e( 'Live search', 'multilingualpress' );
						?>
					</label>
					<?php
					print $this->get_search_input( $this->search_input_id );
					?>

					<ul class="mlp_search_results"
						id="mlp_search_results_<?php print $this->remote_blog_id; ?>">
						<?php
						$this->updater->update( 'default.remote.posts' );
						?>
					</ul>
				</div>
				<p class="clear">
					<?php
					$data_attrs = $this->add_id_values( '' );
					?>
					<input type="submit"
						   class="button button-primary mlp_rsc_save_reload"
						   value="<?php
						   esc_attr_e( 'Save and reload this page', 'multilingualpress' );
						   ?>" <?php print $data_attrs; ?>">
					<span class="description"><?php
						esc_html_e( 'Please save other changes first separately.', 'multilingualpress' );
						?></span>
				</p>
			</div>
		</div>
	<?php
	}

	public function print_jquery() {
		?>
		<script>
			jQuery('.mlp_search_field').mlp_search({
				action:           'mlp_rsc_search',
				remote_blog_id:    <?php print $this->remote_blog_id; ?>,
				result_container: '#mlp_search_results_<?php print $this->remote_blog_id; ?>',
				search_field:     '#<?php print $this->search_input_id; ?>'
			});
		</script>
	<?php
	}

	/**
	 * @param string $id
	 * @return string
	 */
	private function get_search_input( $id ) {

		$input = '<input type="search" class="mlp_search_field" id="' . $id . '"';
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
			'source_post_id' => $this->post->ID,
			'source_blog_id' => $this->blog_id,
			'remote_blog_id' => $this->remote_blog_id,
			'remote_post_id' => $this->remote_post_id,
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
			'<label for="%5$s_%1$s">
				<input type="radio" name="%4$s" id="%5$s_%1$s" value="%1$s"%3$s>
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