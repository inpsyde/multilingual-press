<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox\UI;

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
	 * @var bool
	 */
	private $update;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param bool $update
	 */
	public function __construct( bool $update = false ) {

		$this->update = $update;
	}

	/**
	 * @param int           $remote_site_id
	 * @param \WP_Term      $source_term
	 * @param \WP_Term|null $remote_term
	 *
	 * @return string
	 */
	public function main_fields( int $remote_site_id, \WP_Term $source_term, \WP_Term $remote_term = null ): string {

		ob_start();
		?>
		<p><?php $this->select_term_inputs( $remote_site_id, $source_term, $remote_term ) ?></p>
		<p><?php $this->create_term_inputs( $remote_site_id, $source_term, $remote_term ) ?></p>
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

		$select_id         = self::RELATED_TERM_SELECT . "-{$remote_site_id}]";
		$select_aria_label = esc_html__( 'Use the dropdown to select the term for translation', 'multilingualpress' );
		$option_none_value = '';
		$option_none_label = '---';
		if ( $remote_term ) {
			$option_none_value = '-1';
			$option_none_label = '<b>' . esc_html__( 'Remove connected term', 'multilingualpress' ) . '</b>';
		}
		?>
		<label for="<?= $select_id ?>" class="screen-reader-text"><?= $select_aria_label ?></label>
		<?php
		$output .= wp_dropdown_categories(
			[
				'show_option_none'  => $option_none_label,
				'option_none_value' => $option_none_value,
				'orderby'           => 'name',
				'order'             => 'ASC',
				'hide_empty'        => false,
				'echo'              => false,
				'selected'          => $remote_term ? $remote_term->term_id : '',
				'name'              => self::RELATED_TERM_SELECT . "[{$remote_site_id}]",
				'id'                => $select_id,
				'class'             => 'regular-text',
				'taxonomy'          => $source_term->taxonomy,
				'value_field'       => 'term_id',
			]
		);

		return $output;
	}

	/**
	 * @param int           $remote_site_id
	 * @param \WP_Term      $source_term
	 * @param \WP_Term|null $remote_term
	 *
	 * @return string
	 */
	private function create_term_inputs(
		int $remote_site_id,
		\WP_Term $source_term,
		\WP_Term $remote_term = null
	): string {

		$output = $this->operation_select_input( self::RELATED_TERM_DO_CREATE, $remote_site_id, $remote_term );

		$create_id = self::RELATED_TERM_CREATE . "-{$remote_site_id}]";
		$create_aria_label = esc_html__( 'Type here the term translation', 'multilingualpress' );

		ob_start();
		?>
		<label for="<?= $create_id ?>" class="screen-reader-text"><?= $create_aria_label ?></label>
		<input
			type="text"
			id="<?= $create_id ?>"
			name="<?= self::RELATED_TERM_CREATE . "[{$remote_site_id}]" ?>"
			class="regular-text">
		<?php

		return $output . ob_get_clean();
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

		$operation_id   = self::RELATED_TERM_OPERATION . "-{$remote_site_id}]";
		$operation_name = self::RELATED_TERM_OPERATION . "[{$remote_site_id}]";
		$select_label   = esc_html_x( 'Select existing term', 'Term translation input', 'multilingualpress' );

		$checked = $operation === self::RELATED_TERM_DO_CREATE
			? null === $remote_term
			: null !== $remote_term;

		ob_start();
		?>
		<label for="<?= $operation_id ?>-select">
			<input
				type="radio"
				name="<?= $operation_name ?>"
				id="<?= $operation_id ?>-select"
				value="<?= $operation ?>"
				<?= $checked ? ' checked="checked"' : '' ?>/> <?= $select_label ?>
		</label>
		<?php
		$input_markup = ob_get_clean();
		if ( $this->update ) {
			$input_markup .= '<br>';
		}

		return $input_markup;
	}

}
