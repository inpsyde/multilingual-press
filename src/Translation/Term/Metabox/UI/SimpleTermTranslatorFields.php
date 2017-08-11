<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox\UI;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;

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
	 * @param ServerRequest $server_request
	 */
	public function __construct( ServerRequest $server_request ) {

		$this->server_request = $server_request;
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
		<p><?php echo $this->select_term_inputs( $remote_site_id, $source_term, $remote_term ) ?></p>
		<p><?php echo $this->create_term_inputs( $remote_site_id, $remote_term ) ?></p>
		<?php

		return ob_get_clean();
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

		$output = $this->operation_select_input( self::RELATED_TERM_DO_SELECT, $remote_site_id, $remote_term );

		$select_id = self::RELATED_TERM_SELECT . "-{$remote_site_id}";

		if ( $remote_term ) {
			$option_none_value = '-1';
			$option_none_label = '<strong>' . esc_html__( 'Remove connected term', 'multilingualpress' ) . '</strong>';
		} else {
			$option_none_value = '';
			$option_none_label = '---';
		}
		?>
		<label for="<?php echo esc_attr( $select_id ); ?>"
			class="screen-reader-text">
			<?php esc_html_e( 'Use the dropdown to select a term for translation', 'multilingualpress' ); ?>
		</label>
		<?php
		switch_to_blog( $remote_site_id );

		$output .= wp_dropdown_categories( [
			'show_option_none'  => $option_none_label,
			'option_none_value' => $option_none_value,
			'orderby'           => 'name',
			'order'             => 'ASC',
			'hide_empty'        => false,
			'echo'              => false,
			'selected'          => $remote_term ? $remote_term->term_taxonomy_id : '',
			'name'              => self::RELATED_TERM_SELECT . "[{$remote_site_id}]",
			'id'                => $select_id,
			'class'             => 'regular-text',
			'taxonomy'          => $source_term->taxonomy,
			'value_field'       => 'term_taxonomy_id',
		] );

		restore_current_blog();

		return $output;
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
			<?php esc_html_e( 'Type here the term translation', 'multilingualpress' ); ?>
		</label>
		<input
			type="text"
			id="<?php echo esc_attr( $create_id ); ?>"
			name="<?php echo esc_attr( self::RELATED_TERM_CREATE . "[{$remote_site_id}]" ); ?>"
			class="regular-text">
		<?php
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

		$operation_id = self::RELATED_TERM_OPERATION . "-{$remote_site_id}-select";

		$create = $operation === self::RELATED_TERM_DO_CREATE;

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
}
