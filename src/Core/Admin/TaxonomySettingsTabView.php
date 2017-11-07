<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUIRegistry;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\UIAwareMetaBoxRegistrar;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Core\TaxonomyRepository;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Taxonomy settings tab view.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class TaxonomySettingsTabView implements SettingsPageView {

	/**
	 * @var UIAwareMetaBoxRegistrar
	 */
	private $meta_box_registrar;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var TaxonomyRepository
	 */
	private $repository;

	/**
	 * @var MetaBoxUI[]
	 */
	private $ui_objects;

	/**
	 * @var MetaBoxUIRegistry
	 */
	private $ui_registry;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TaxonomyRepository      $repository         Taxonomy repository object.
	 * @param Nonce                   $nonce              Nonce object.
	 * @param MetaBoxUIRegistry       $ui_registry        Meta box UI registry object.
	 * @param UIAwareMetaBoxRegistrar $meta_box_registrar Meta box registrar object.
	 */
	public function __construct(
		TaxonomyRepository $repository,
		Nonce $nonce,
		MetaBoxUIRegistry $ui_registry,
		UIAwareMetaBoxRegistrar $meta_box_registrar
	) {

		$this->repository = $repository;

		$this->nonce = $nonce;

		$this->ui_registry = $ui_registry;

		$this->meta_box_registrar = $meta_box_registrar;
	}

	/**
	 * Renders the markup.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render() {

		$taxonomies = $this->repository->get_available_taxonomies();
		if ( ! $taxonomies ) {
			return;
		}

		nonce_field( $this->nonce );
		?>
		<table class="widefat mlp-settings-table mlp-taxonomy-settings">
			<tbody>
			<?php array_walk( $taxonomies, [ $this, 'render_table_row' ] ); ?>
			</tbody>
			<thead>
			<?php $this->render_table_headings(); ?>
			</thead>
			<tfoot>
			<?php $this->render_table_headings(); ?>
			</tfoot>
		</table>
		<?php
	}

	/**
	 * Returns the input ID for the given taxonomy slug and settings field name.
	 *
	 * @param string $slug  Taxonomy slug.
	 * @param string $field Optional. Settings field name. Defaults to empty string.
	 *
	 * @return string Input ID.
	 */
	private function get_id( string $slug, string $field = '' ) {

		return "mlp-taxonomy-{$slug}" . ( $field ? "|{$field}" : '' );
	}

	/**
	 * Returns the input name for the given taxonomy slug and settings field name.
	 *
	 * @param string $slug  Taxonomy slug.
	 * @param string $field Settings field name.
	 *
	 * @return string Input name.
	 */
	private function get_name( string $slug, string $field ) {

		return TaxonomySettingsUpdater::SETTINGS_NAME . "[{$slug}][{$field}]";
	}

	/**
	 * Returns the available term meta box UI objects.
	 *
	 * @return MetaBoxUI[] Term meta box UI objects.
	 */
	private function get_ui_objects() {

		if ( ! isset( $this->ui_objects ) ) {
			$this->ui_objects = $this->ui_registry->get_objects( $this->meta_box_registrar );
		}

		return $this->ui_objects;
	}

	/**
	 * Renders the option HTML element for tht given UI object.
	 *
	 * @param MetaBoxUI $ui        UI object.
	 * @param string    $id        UI ID.
	 * @param string    $active_ui Active UI ID.
	 *
	 * @return void
	 */
	private function render_option( MetaBoxUI $ui, string $id, string $active_ui ) {

		?>
		<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $id, $active_ui ); ?>>
			<?php echo esc_html( $ui->name() ); ?>
		</option>
		<?php
	}

	/**
	 * Renders the table headings.
	 *
	 * @return void
	 */
	private function render_table_headings() {

		?>
		<tr>
			<th scope="col"></th>
			<th scope="col"><?php esc_html_e( 'Taxonomy', 'multilingualpress' ); ?></th>
			<th scope="col"><?php esc_html_e( 'User Interface', 'multilingualpress' ); ?></th>
		</tr>
		<?php
	}

	/**
	 * Renders a table row element according to the given data.
	 *
	 * @param \WP_Taxonomy $taxonomy Taxonomy object.
	 * @param string       $slug     Taxonomy slug.
	 *
	 * @return void
	 */
	private function render_table_row( \WP_Taxonomy $taxonomy, string $slug ) {

		$is_active = $this->repository->is_taxonomy_active( $slug );
		?>
		<tr class="<?php echo esc_attr( $is_active ? 'active' : 'inactive' ); ?>">
			<?php $field = TaxonomySettingsUpdater::SETTINGS_FIELD_ACTIVE; ?>
			<?php $id = $this->get_id( $slug ); ?>
			<th class="check-column" scope="row">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_name( $slug, $field ) ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>" title="<?php echo esc_html( $slug ); ?>"
					<?php checked( $is_active ); ?>>
			</th>
			<td>
				<label for="<?php echo esc_attr( $id ); ?>" class="mlp-block-label">
					<strong class="mlp-setting-name" title="<?php echo esc_html( $slug ); ?>">
						<?php echo esc_html( $taxonomy->labels->name ); ?>
					</strong>
				</label>
			</td>
			<?php $field = TaxonomySettingsUpdater::SETTINGS_FIELD_UI; ?>
			<?php $id = $this->get_id( $slug, $field ); ?>
			<td>
				<label for="<?php echo esc_attr( $id ); ?>" class="mlp-block-label">
					<?php $this->render_ui_select( $slug, $id ); ?>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * Renders the UI select HTML element.
	 *
	 * @param string $slug Taxonomy slug.
	 * @param string $id   Input ID.
	 *
	 * @return void
	 */
	private function render_ui_select( string $slug, string $id ) {

		$name = $this->get_name( $slug, TaxonomySettingsUpdater::SETTINGS_FIELD_UI );
		?>
		<select name="<?php echo esc_attr( $name ); ?>"
			title="<?php esc_attr_e( 'User interface', 'multilingualpress' ); ?>" id="<?php echo esc_attr( $id ); ?>"
		>
			<?php
			$ui_objects = $this->get_ui_objects();
			array_walk( $ui_objects, [ $this, 'render_option' ], $this->repository->get_taxonomy_ui( $slug ) );
			?>
		</select>
		<?php
	}
}
