<?php # -*- coding: utf-8 -*-
/**
 * Mlp_Term_Translation_Selector
 *
 * @version 2014.09.19
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Term_Translation_Selector {

	/**
	 * @var Mlp_Term_Translation_Presenter
	 */
	private $presenter;

	/**
	 * @param Mlp_Term_Translation_Presenter $presenter
	 */
	public function __construct( Mlp_Term_Translation_Presenter $presenter ) {

		$this->presenter = $presenter;
	}

	/**
	 * @return bool
	 */
	public function print_fieldset_id() {

		print 'mlp_term_translation';

		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function print_title() {

		print $this->presenter->get_group_title();

		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function print_table() {

		$sites = $this->presenter->get_site_languages();
		print $this->presenter->get_nonce_field();
		$this->print_style();
		?>

		<table class="mlp_term_selections">
			<?php
			foreach ( $sites as $site_id => $language ) {

				$key          = $this->presenter->get_key_base( $site_id );
				$label_id     = $this->get_label_id( $key );
				$terms        = $this->presenter->get_terms_for_site( $site_id );
				$current_term = $this->get_current_term( $site_id );
				?>
				<tr>
					<th>
						<label for="<?php print $label_id; ?>"><?php
							print $language;
						?></label>
					</th>
					<td>
						<select name="<?php print $key; ?>" id="<?php print $label_id; ?>">
							<option value="0" class="mlp_empty_option"><?php
								esc_html_e( 'No translation', 'multilingualpress' );
							?></option>
							<?php
							foreach ( $terms as $term_id => $term_name )
								print $this->get_option_element(
									$term_id,
									$term_name,
									$current_term
								);
							?>
						</select>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php

		return TRUE;
	}

	/**
	 * @param  int $site_id
	 * @return int
	 */
	private function get_current_term( $site_id ) {

		if ( empty ( $_GET[ 'tag_ID' ] ) )
			return 0;

		return $this->presenter->get_current_term(
			$site_id,
			(int) $_GET[ 'tag_ID' ]
		);
	}

	/**
	 * Print inline stylesheet.
	 *
	 * @return void
	 */
	private function print_style() {
		?>
		<style>
			#<?php $this->print_fieldset_id(); ?> {
				margin: 1em 0;
			}
			#<?php $this->print_fieldset_id(); ?> legend {
				font-weight: bold;
			}
			.mlp_term_selections th {
				text-align: right;
			}
			.mlp_term_selections select {
				width: 20em;
			}
			.mlp_empty_option {
				font-style: italic;
			}
			.mlp_term_selections th, .mlp_term_selections td {
				padding: 0 5px;
				vertical-align: middle;
				font-weight: normal;
				width: auto;
			}
		</style>
	<?php
	}

	/**
	 * @param  int $term_id
	 * @param  string $term_name
	 * @param  int $current_term
	 * @return string
	 */
	private function get_option_element( $term_id, $term_name, $current_term ) {

		$selected = $current_term === $term_id ? 'selected="selected"' : '';
		return sprintf(
			'<option value="%1$d" %2$s>%3$s</option>',
			$term_id,
			$selected,
			$term_name
		);
	}

	/**
	 * Make sure we have a HTML-4 compatible id attribute.
	 *
	 * @param  string $key
	 * @return string
	 */
	private function get_label_id( $key ) {

		return str_replace( array( '[', ']' ), '', $key );
	}
}