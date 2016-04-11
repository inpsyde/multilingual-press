<?php
/**
 * Backend view for nav menu management.
 *
 * @version 2014.05.13
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */

class Mlp_Simple_Nav_Menu_Selectors {

	/**
	 * @var Mlp_Nav_Menu_Selector_Data_Interface
	 */
	private $data;

	/**
	 * @param Mlp_Nav_Menu_Selector_Data_Interface $data
	 */
	public function __construct( Mlp_Nav_Menu_Selector_Data_Interface $data ) {
		$this->data = $data;
	}

	/**
	 * @return void
	 */
	public function show_available_languages() {

		$list_id = $this->data->get_list_id();
		?>
		<div id="mlp-<?php echo esc_attr( $list_id ); ?>">
			<?php
			$this->print_item_list( $list_id );
			$this->print_button_controls( $list_id );
			?>
		</div>
	<?php
	}

	/**
	 * @return void
	 */
	public function show_selected_languages() {

		$menu_items = $this->data->get_ajax_menu_items();
		if ( empty( $menu_items ) ) {
			wp_send_json_error();
		}

		// Needed for the walker.
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

		$data = walk_nav_menu_tree( $menu_items, 0, (object) array(
			'after'       => '',
			'before'      => '',
			'link_after'  => '',
			'link_before' => '',
			'walker'      => new Walker_Nav_Menu_Edit(),
		) );
		wp_send_json_success( $data );
	}

	/**
	 * @param  string $list_id
	 * @return void
	 */
	private function print_button_controls( $list_id ) {
		?>
		<p class="button-controls">
			<?php
			$this->print_select_all( $list_id );
			$this->print_add_button();
			?>
		</p>
		<?php
	}

	/**
	 * @param  string $list_id
	 * @return void
	 */
	private function print_select_all( $list_id ) {

		$url = $this->get_select_all_url( $list_id );
		?>
		<span class="list-controls">
			<a href="<?php echo esc_url( $url ); ?>" class="select-all">
				<?php esc_html_e( 'Select All', 'multilingual-press' ); ?>
			</a>
		</span>
		<?php
	}

	/**
	 * @param  string $list_id
	 * @return void
	 */
	private function print_item_list( $list_id ) {

		$items = $this->data->get_list();

		if ( empty ( $items ) ) {
			esc_html_e( 'No languages found', 'multilingual-press' );
			return;
		}
		// class "tabs-panel-active" is needed to make "Select All" work
		?>
		<ul id="<?php echo esc_attr( $list_id ); ?>" class="tabs-panel-active">
			<?php
			foreach ( $items as $value => $text )
				$this->print_item( $value, $text );
			?>
		</ul>
	<?php
	}

	/**
	 * @param  string $value
	 * @param  string $text
	 * @return void
	 */
	private function print_item( $value, $text ) {
		?>
		<li>
			<label class="menu-item-title">
				<input type="checkbox" value ="<?php echo esc_attr( $value ); ?>">
				&nbsp;<?php echo esc_attr( $text ); ?>
			</label>
		</li>
	<?php

	}

	/**
	 * @return void
	 */
	private function print_add_button() {

		$button_id         = $this->data->get_button_id();
		$button_attributes = array (
			'id' => "submit-$button_id"
		);

		if ( ! $this->data->has_menu() )
			$button_attributes[ 'disabled' ] = 'disabled';
		?>

		<span class="add-to-menu">
				<?php
				submit_button(
					esc_attr__( 'Add to Menu', 'multilingual-press' ),
					'button-secondary submit-add-to-menu right',
					"add-$button_id-item",
					FALSE,
					$button_attributes
				);
				?>
			<span class="spinner"></span>
		</span>
		<?php
	}

	/**
	 * Create the URL for the "Select All" link.
	 *
	 * @param  string $list_id
	 * @return string
	 */
	private function get_select_all_url( $list_id ) {

		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$base = remove_query_arg( $removed_args );
		$url  = add_query_arg(
			array(
				'languages-tab' => 'all',
				'selectall'     => 1,
			),
			$base
		);

		return esc_url( $url ) . "#mlp-$list_id";
	}

}
