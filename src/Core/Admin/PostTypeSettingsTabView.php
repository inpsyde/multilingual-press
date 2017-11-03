<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Core\PostTypeRepository;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Post type settings tab view.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
final class PostTypeSettingsTabView implements SettingsPageView {

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var \WP_Post_Type[]
	 */
	private $post_types;

	/**
	 * @var PostTypeRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param PostTypeRepository $repository Post type repository object.
	 * @param Nonce              $nonce      Nonce object.
	 */
	public function __construct( PostTypeRepository $repository, Nonce $nonce ) {

		$this->repository = $repository;

		$this->nonce = $nonce;
	}

	/**
	 * Renders the markup.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render() {

		if ( ! isset( $this->post_types ) ) {
			$this->post_types = $this->repository->get_available_post_types();
		}

		if ( ! $this->post_types ) {
			return;
		}

		$supported_post_types = $this->repository->get_supported_post_types();

		nonce_field( $this->nonce );
		?>
		<table>
			<tbody>
			<?php array_walk( $this->post_types, [ $this, 'render_table_row' ], $supported_post_types ); ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Renders a table row element according to the given data.
	 *
	 * @param \WP_Post_Type $post_type            Post type object.
	 * @param string        $slug                 Post type slug.
	 * @param int[]         $supported_post_types Supported post type settings.
	 *
	 * @return void
	 */
	private function render_table_row( \WP_Post_Type $post_type, string $slug, array $supported_post_types ) {

		/* TODO: Completely update the UI (e.g., integrate a select for the translation meta box UI). */

		$name = PostTypeSettingsUpdater::SETTINGS_NAME;

		$id = "mlp-post-type-{$slug}";

		$post_type_setting = empty( $supported_post_types[ $slug ] )
			? PostTypeRepository::CPT_INACTIVE
			: (int) $supported_post_types[ $slug ];
		?>
		<tr>
			<td>
				<label for="<?php echo esc_attr( $id ); ?>" class="mlp-block-label">
					<?php
					$this->render_checkbox(
						"{$name}[{$slug}]",
						$id,
						PostTypeRepository::CPT_INACTIVE !== $post_type_setting
					);
					?>
					<?php echo esc_html( $post_type->labels->name ); ?>
				</label>
			</td>
			<td>
				<label for="<?php echo esc_attr( $id ); ?>|links" class="mlp-block-label">
					<?php
					$this->render_checkbox(
						"{$name}[{$slug}|links]",
						"{$id}|links",
						PostTypeRepository::CPT_QUERY_BASED === $post_type_setting
					);
					?>
					<?php esc_html_e( 'Use dynamic permalinks', 'multilingualpress' ); ?>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * Renders a checkbox element according to the given data.
	 *
	 * @param string $name    Name attribute value.
	 * @param string $id      ID attribute value.
	 * @param bool   $checked Checked state.
	 *
	 * @return void
	 */
	private function render_checkbox( string $name, string $id, bool $checked ) {

		printf(
			'<input type="checkbox" name="%1$s" value="1" id="%2$s"%3$s>',
			esc_attr( $name ),
			esc_attr( $id ),
			checked( $checked, true, false )
		);
	}
}
