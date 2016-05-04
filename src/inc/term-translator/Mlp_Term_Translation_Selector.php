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
	 * @type array
	 */
	private $related_sites = array();

	/**
	 * @param Mlp_Term_Translation_Presenter $presenter
	 */
	public function __construct( Mlp_Term_Translation_Presenter $presenter ) {

		$this->presenter     = $presenter;
		$this->related_sites = $presenter->get_site_languages();
	}

	/**
	 * @return string
	 */
	public function get_fieldset_id() {

		return 'mlp_term_translation';
	}

	/**
	 * @return bool
	 */
	public function print_fieldset_id() {

		echo $this->get_fieldset_id();

		return TRUE;
	}

	/**
	 * @return string
	 */
	public function get_title() {

		return empty( $this->related_sites ) ? '' : $this->presenter->get_group_title();
	}

	/**
	 * @return bool
	 */
	public function print_title() {

		echo $this->get_title();

		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function print_table() {

		if ( empty( $this->related_sites ) ) {
			return FALSE;
		}

		echo $this->presenter->get_nonce_field();

		$this->print_style();
		?>
		<table id="mlp-term-translations">
			<?php foreach ( $this->related_sites as $site_id => $language ) : ?>
				<?php
				$key                = $this->presenter->get_key_base( $site_id );
				$label_id           = $this->get_label_id( $key );
				$terms              = $this->presenter->get_terms_for_site( $site_id );
				$current_term       = $this->get_current_term( $site_id );
				$empty_option_value = $current_term > 0 ? 0 : -1;
				?>
				<tr>
					<th>
						<label for="<?php echo esc_attr( $label_id ); ?>"><?php echo esc_html( $language ); ?></label>
					</th>
					<td>
						<?php if ( empty( $terms ) ) : ?>
							<?php echo $this->get_no_terms_found_message( $site_id ); ?>
						<?php else : ?>
							<select
								name="<?php echo esc_attr( $key ); ?>"
								id="<?php echo esc_attr( $label_id ); ?>"
								autocomplete="off">
								<option value="<?php echo esc_attr( $empty_option_value ); ?>" class="mlp_empty_option">
									<?php esc_html_e( 'No translation', 'multilingual-press' ); ?>
								</option>
								<?php $this->print_term_options( $terms, $current_term, $site_id ); ?>
							</select>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
		return TRUE;
	}

	/**
	 * Create the message to display when there are no terms on the other site.
	 *
	 * @param int $site_id Blog ID.
	 *
	 * @return string
	 */
	private function get_no_terms_found_message( $site_id ) {

		$taxonomy_name = $this->presenter->get_taxonomy();

		$admin_url = get_admin_url( $site_id, 'edit-tags.php' );
		$taxonomy_edit_url = add_query_arg(
			'taxonomy',
			$taxonomy_name,
			$admin_url
		);
		$url = esc_url( $taxonomy_edit_url );

		$taxonomy_object = get_taxonomy( $taxonomy_name );
		$text = isset( $taxonomy_object->labels->not_found )
			? esc_html( $taxonomy_object->labels->not_found )
			: esc_html__( 'No terms found.', 'multilingual-press' );

		return sprintf( '<p><a href="%1$s">%2$s</a></p>', $url, $text );
	}

	/**
	 * Return the term taxonomy ID for the currently saved term.
	 *
	 * @param int $site_id Blog ID.
	 *
	 * @return int
	 */
	private function get_current_term( $site_id ) {

		if ( empty( $_GET[ 'tag_ID' ] ) ) {
			return 0;
		}

		return $this->presenter->get_current_term( $site_id, (int) $_GET[ 'tag_ID' ] );
	}

	/**
	 * Render the option tags for the given terms.
	 *
	 * @param int   $current_term Currently saved term taxonomy ID.
	 * @param array $terms        Term names.
	 * @param int   $site_id      Blog ID.
	 *
	 * @return void
	 */
	private function print_term_options( $terms, $current_term, $site_id ) {

		foreach ( $terms as $term_taxonomy_id => $term_name ) {
			echo $this->get_option_element(
				$term_taxonomy_id,
				$term_name,
				$current_term,
				$this->presenter->get_relation_id( $site_id, $term_taxonomy_id )
			);
		}
	}

	/**
	 * Print inline stylesheet.
	 *
	 * @return void
	 */
	private function print_style() {
		?>
		<style>
			#mlp-term-translations th {
				text-align: right;
			}
			#mlp-term-translations select {
				width: 20em;
			}
			.mlp_empty_option {
				font-style: italic;
			}
			#mlp-term-translations th, #mlp-term-translations td {
				padding: 0 5px;
				width: auto;
				vertical-align: middle;
				font-weight: normal;
			}
		</style>
	<?php
	}

	/**
	 * Return the optin tag for the given term.
	 *
	 * @param int    $term_taxonomy_id Term taxonomy ID.
	 * @param string $term_name        Term name.
	 * @param int    $current_term     Currently saved term taxonomy ID.
	 * @param string $relation_id      Relation ID.
	 *
	 * @return string
	 */
	private function get_option_element( $term_taxonomy_id, $term_name, $current_term, $relation_id ) {

		$is_current = $current_term === $term_taxonomy_id;

		return sprintf(
			'<option value="%1$d" data-relation="%4$s"%2$s>%3$s</option>',
			$term_taxonomy_id,
			$is_current ? ' selected="selected"' : '',
			$term_name,
			$relation_id
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
