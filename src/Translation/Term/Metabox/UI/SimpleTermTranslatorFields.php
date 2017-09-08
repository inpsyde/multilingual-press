<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox\UI;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Term\TermOptionsRepository;

/**
 * @package Inpsyde\MultilingualPress\Translation\Term\MetaBox\UI
 * @since   3.0.0
 */
class SimpleTermTranslatorFields {

	/**
	 * Input name
	 *
	 * @var string
	 */
	const RELATED_TERM_OPERATION = 'mlp_related_term_op';

	/**
	 * A possible input value for RELATED_TERM_OPERATION input
	 *
	 * @var string
	 */
	const RELATED_TERM_DO_CREATE = 'create';

	/**
	 * A possible input value for RELATED_TERM_OPERATION input
	 *
	 * @var string
	 */
	const RELATED_TERM_DO_SELECT = 'select';

	/**
	 * Input name
	 *
	 * @var string
	 */
	const RELATED_TERM_SELECT = 'mlp_related_term_select';

	/**
	 * Input name
	 *
	 * @var string
	 */
	const RELATED_TERM_CREATE = 'mlp_related_term_create';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var TermOptionsRepository
	 */
	private $repository;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * @var bool
	 */
	private $update = false;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ServerRequest         $server_request
	 * @param TermOptionsRepository $repository
	 * @param ContentRelations      $content_relations
	 * @param AssetManager          $asset_manager
	 */
	public function __construct(
		ServerRequest $server_request,
		TermOptionsRepository $repository,
		ContentRelations $content_relations,
		AssetManager $asset_manager
	) {

		$this->server_request = $server_request;

		$this->repository = $repository;

		$this->content_relations = $content_relations;

		$this->asset_manager = $asset_manager;
	}

	/**
	 * @param bool $update
	 */
	public function set_update( bool $update ) {

		$this->update = $update;
	}

	/**
	 * @param \WP_Term      $source_term
	 * @param int           $remote_site_id
	 * @param \WP_Term|null $remote_term
	 *
	 * @return string
	 */
	public function main_fields( \WP_Term $source_term, int $remote_site_id, \WP_Term $remote_term = null ): string {

		ob_start();
		?>
		<p><?php echo $this->create_term_inputs( $remote_site_id, $remote_term ); ?></p>
		<p><?php echo $this->select_term_inputs( $remote_site_id, $source_term, $remote_term ); ?></p>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param int           $remote_site_id
	 * @param \WP_Term|null $remote_term
	 *
	 * @return string
	 */
	private function create_term_inputs(
		int $remote_site_id,
		\WP_Term $remote_term = null
	): string {

		$output = $this->operation_select_input( self::RELATED_TERM_DO_CREATE, $remote_site_id, $remote_term );

		$create_id = self::RELATED_TERM_CREATE . "-{$remote_site_id}";

		ob_start();
		?>
		<label for="<?php echo esc_attr( $create_id ); ?>" class="screen-reader-text">
			<?php esc_html_e( 'Enter name here', 'multilingualpress' ); ?>
		</label>
		<input
			type="text"
			id="<?php echo esc_attr( $create_id ); ?>"
			name="<?php echo esc_attr( self::RELATED_TERM_CREATE . "[{$remote_site_id}]" ); ?>"
			data-site="<?php echo esc_attr( $remote_site_id ); ?>"
			class="regular-text mlp-term-input">
		<?php
		$output .= ob_get_clean();

		return $output;
	}

	/**
	 * @param int           $remote_site_id
	 * @param \WP_Term      $source_term
	 * @param \WP_Term|null $remote_term
	 *
	 * @return string
	 */
	private function select_term_inputs(
		int $remote_site_id,
		\WP_Term $source_term,
		\WP_Term $remote_term = null
	): string {

		$taxonomy = $source_term->taxonomy;

		$options = $this->repository->get_terms_for_site( $remote_site_id, $taxonomy );

		$output = $this->operation_select_input( self::RELATED_TERM_DO_SELECT, $remote_site_id, $remote_term );

		$select_id = self::RELATED_TERM_SELECT . "-{$remote_site_id}";

		ob_start();
		?>
		<label for="<?php echo esc_attr( $select_id ); ?>"
			class="screen-reader-text">
			<?php esc_html_e( 'Use the dropdown to select a term for translation', 'multilingualpress' ); ?>
		</label>
		<?php
		if ( $options ) {
			$this->asset_manager->enqueue_script( 'multilingualpress-admin' );

			$option_none_value = $remote_term ? '-1' : '';

			$option_none_text = $remote_term ? __( 'Remove relationship', 'multilingualpress' ) : '';

			$current_term_taxonomy_id = (int) ( $remote_term->term_taxonomy_id ?? 0 );
			?>
			<select
				name="<?php echo esc_attr( self::RELATED_TERM_SELECT . "[{$remote_site_id}]" ); ?>"
				id="<?php echo esc_attr( $select_id ); ?>"
				class="regular-text mlp-term-select"
				data-site="<?php echo esc_attr( $remote_site_id ); ?>"
				autocomplete="off">
				<option value="<?php echo esc_attr( $option_none_value ); ?>" class="option-none">
					<?php echo esc_html( $option_none_text ); ?>
				</option>
				<?php $this->render_term_options( $options, $current_term_taxonomy_id, $remote_site_id ); ?>
			</select>
			<?php
		} else {
			$text = get_taxonomy( $taxonomy )->labels->not_found ?? __( 'No terms found.', 'multilingualpress' );

			$url = add_query_arg( compact( 'taxonomy' ), get_admin_url( $remote_site_id, 'edit-tags.php' ) );

			printf(
				'<p><a href="%2$s">%1$s</a></p>',
				esc_html( $text ),
				esc_url( $url )
			);
		}

		$output .= ob_get_clean();

		return $output;
	}

	/**
	 * @param string        $operation
	 * @param int           $remote_site_id
	 * @param \WP_Term|null $remote_term
	 *
	 * @return string
	 */
	private function operation_select_input(
		string $operation,
		int $remote_site_id,
		\WP_Term $remote_term = null
	): string {

		$operation_id = self::RELATED_TERM_OPERATION . "-{$remote_site_id}-{$operation}";

		$create = self::RELATED_TERM_DO_CREATE === $operation;

		$checked = $create
			? null === $remote_term
			: null !== $remote_term;

		ob_start();
		?>
		<label for="<?php echo esc_attr( $operation_id ); ?>">
			<input
				type="radio"
				name="<?php echo esc_attr( self::RELATED_TERM_OPERATION . "[{$remote_site_id}]" ); ?>"
				id="<?php echo esc_attr( $operation_id ); ?>"
				value="<?php echo esc_attr( $operation ); ?>"<?php checked( $checked ); ?>/>
			<?php
			echo $create
				? esc_html__( 'Create new term', 'multilingualpress' )
				: esc_html__( 'Select existing term', 'multilingualpress' );
			?>
		</label>
		<?php
		$input_markup = ob_get_clean();
		if ( $this->update ) {
			$input_markup .= '<br>';
		}

		return $input_markup;
	}

	/**
	 * Renders the given term options.
	 *
	 * @param string[] $options Term options.
	 * @param int      $current Currently selected term taxonomy ID.
	 * @param int      $site_id Site ID.
	 *
	 * @return void
	 */
	private function render_term_options( array $options, int $current, int $site_id ) {

		foreach ( $options as $term_taxonomy_id => $term_name ) {
			printf(
				'<option value="%2$d" data-relation="%4$s"%3$s>%1$s</option>',
				$term_name,
				$term_taxonomy_id,
				selected( $term_taxonomy_id, $current, false ),
				$this->get_relation_id( $site_id, $term_taxonomy_id )
			);
		}
	}

	/**
	 * @param int $site_id
	 * @param int $term_taxonomy_id
	 *
	 * @return string
	 */
	private function get_relation_id( $site_id, $term_taxonomy_id ) {

		// $translation_ids = $this->content_relations->get_existing_translation_ids(
		// $site_id,
		// 0,
		// $term_taxonomy_id,
		// 0,
		// 'term'
		// );
		// TODO: Revisit and correct the following! This is only a quick-fix to test the post translation.
		$translation_ids = [
			'ml_source_blogid'    => 0,
			'ml_source_elementid' => 0,
		];
		if ( ! $translation_ids ) {
			return '';
		}

		$relation = reset( $translation_ids );

		return "{$relation['ml_source_blogid']}-{$relation['ml_source_elementid']}";
	}
}
