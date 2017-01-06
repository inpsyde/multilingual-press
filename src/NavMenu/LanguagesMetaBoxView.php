<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\NavMenu;

use Inpsyde\MultilingualPress\Common\Admin\MetaBoxModel;
use Inpsyde\MultilingualPress\Common\Admin\MetaBoxView;

/**
 * Languages meta box view.
 *
 * @package Inpsyde\MultilingualPress\NavMenu
 * @since   3.0.0
 */
final class LanguagesMetaBoxView implements MetaBoxView {

	/**
	 * @var string
	 */
	private $id;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param MetaBoxModel $model Meta box model object.
	 */
	public function __construct( MetaBoxModel $model ) {

		$this->id = $model->id();
	}

	/**
	 * Renders the HTML.
	 *
	 * @since 3.0.0
	 *
	 * @param object $object Object.
	 * @param array  $args   Arguments.
	 *
	 * @return void
	 */
	public function render( $object, array $args ) {

		?>
		<div id="<?php echo esc_attr( $this->id ); ?>-container">
			<?php $this->render_items(); ?>
			<?php $this->render_button_controls(); ?>
		</div>
		<?php
	}

	/**
	 * Renders all language items.
	 *
	 * @return void
	 */
	private function render_items() {

		$items = \Inpsyde\MultilingualPress\get_available_language_names();
		if ( ! $items ) {
			esc_html_e( 'No items.', 'multilingual-press' );

			return;
		}
		?>
		<div id="tabs-panel-<?php echo esc_attr( $this->id ); ?>" class="tabs-panel tabs-panel-active">
			<ul id="<?php echo esc_attr( $this->id ); ?>" class="form-no-clear">
				<?php array_walk( $items, [ $this, 'render_item' ] ); ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Renders a single item according to the given arguments.
	 *
	 * @param string $title Item title.
	 * @param string $value Item value.
	 *
	 * @return void
	 */
	private function render_item( $title, $value ) {

		?>
		<li>
			<label class="menu-item-title">
				<input type="checkbox" value="<?php echo esc_attr( $value ); ?>" class="menu-item-checkbox">
				&nbsp;<?php echo esc_attr( $title ); ?>
			</label>
		</li>
		<?php
	}

	/**
	 * Renders the button controls HTML.
	 *
	 * @return void
	 */
	private function render_button_controls() {

		$submit_button_attributes = [
			'id' => 'submit-mlp-language',
		];

		if ( empty( $GLOBALS['nav_menu_selected_id'] ) ) {
			$submit_button_attributes['disabled'] = 'disabled';
		}
		?>
		<p class="button-controls wp-clearfix">
			<span class="list-controls">
				<a href="<?php echo esc_url( $this->get_select_all_url() ); ?>" class="select-all aria-button-if-js">
					<?php esc_html_e( 'Select All', 'multilingual-press' ); ?>
				</a>
			</span>
			<span class="add-to-menu">
				<?php
				submit_button(
					__( 'Add to Menu', 'multilingual-press' ),
					'button-secondary submit-add-to-menu right',
					'add-mlp-language-item',
					false,
					$submit_button_attributes
				);
				?>
				<span class="spinner"></span>
			</span>
		</p>
		<?php
	}

	/**
	 * Returns the URL for the "Select All" link.
	 *
	 * @return string URL.
	 */
	private function get_select_all_url() {

		$url = add_query_arg( [
			// Remove...
			'_wpnonce'       => false,
			'action'         => false,
			'customlink-tab' => false,
			'edit-menu-item' => false,
			'menu-item'      => false,
			'page-tab'       => false,
			// Add...
			'languages-tab'  => 'all',
			'selectall'      => 1,
		] );

		return "{$url}#mlp-{$this->id}";
	}
}
