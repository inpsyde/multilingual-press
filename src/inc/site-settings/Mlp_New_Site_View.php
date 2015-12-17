<?php # -*- coding: utf-8 -*-
/**
 * MultilingualPress New Site View
 *
 * This View-Template generates some options Fields for the site-new.php
 *
 * @version 2014.07.09
 * @author  ChriCo
 * @license GPL
 */
class Mlp_New_Site_View {

	/**
	 * @var Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * Constructor.
	 *
	 * @param   Mlp_Language_Api_Interface $language_api
	 * @return  Mlp_New_Site_View
	 */
	public function __construct( Mlp_Language_Api_Interface $language_api ) {
		$this->language_api = $language_api;
	}

	/**
	 * Print tab content and provide two hooks.
	 *
	 * @return void
	 */
	public function render_content() {

		$languages = $this->language_api->get_db()->get_items( array( 'page' => -1 ) );

		$default_language = $this->get_default_language();

		ob_start();
		?>
		<h3>MultilingualPress</h3>
		<table class="form-table">
			<tr class="form-field">
				<td>
					<label for="inpsyde_multilingual_lang">
						<?php
						esc_html_e( 'Language', 'multilingual-press' );
						?>
					</label>
				</td>
				<td>
					<select name="inpsyde_multilingual_lang" id="inpsyde_multilingual_lang" autocomplete="off">
						<option value="-1"><?php esc_html_e( 'Choose language', 'multilingual-press' ); ?></option>
						<?php
						foreach ( $languages as $language ) {

							// missing HTTP code
							if ( empty ( $language->http_name ) )
								continue;

							$selected = selected( $default_language, $language->http_name, false );

							echo '<option value="' . esc_attr( $language->http_name ) . '" ' . $selected . '>'
								. $language->english_name . '/' . $language->native_name
								. '</option>';
						}
						?>
					</select>
				</td>
			</tr>
			<tr class="form-field">
				<td>
					<label for="inpsyde_multilingual_text">
						<?php
						esc_html_e( 'Alternative language title', 'multilingual-press' );
						?>
					</label>
				</td>
				<td>
					<input class="regular-text" type="text" id="inpsyde_multilingual_text" name="inpsyde_multilingual_text" />
					<p class="description"><?php
						esc_html_e(
							'Enter a title here that you want to be displayed in the frontend instead of the default one (i.e. "My English Site")',
							'multilingual-press'
						);
					?></p>
				</td>
			</tr>
			<tr class="form-field">
				<td>
					<label for="inpsyde_multilingual_text">
						<?php
						esc_html_e( 'Relationships', 'multilingual-press' );
						?>
					</label>
				</td>
				<td><?php
					$site_option = get_site_option( 'inpsyde_multilingual', array() );
					foreach ( $site_option as $blog_id => $meta ) {

						$blog_id = (int) $blog_id;
						// Get blog display name
						switch_to_blog( $blog_id );
						$blog_name = esc_html( get_bloginfo( 'Name' ) );
						restore_current_blog();

						$id = "related_blog_$blog_id";
						?>
						<p>
							<label for="<?php echo $id; ?>">
								<input style="width:auto;" id="<?php echo $id; ?>" type="checkbox" name="related_blogs[]" value="<?php echo $blog_id ?>" />
								<?php echo $blog_name; ?> - <?php
								echo Mlp_Helpers::get_blog_language( $blog_id );
								?>
							</label>
						</p>
						<?php
					}
					?>
					<p class="description">
						<?php
							esc_html_e(
								'You can connect this site only to sites with an assigned language. Other sites will not show up here.',
								'multilingual-press'
							);
						?>
					</p>
				</td>
			</tr>

			<?php
			/**
			 * Runs at the end but still inside the new blog fields table.
			 */
			do_action( 'mlp_after_new_blog_fields' );
			?>
		</table>
		<?php
		$template = ob_get_contents();
		// An FTP client might have changed the \n to \r\n.
		$template = str_replace( array ("\n", "\r", "'" ), array( '', '', "\\'" ), $template );
		ob_end_clean();

		?>
		<script>
			( function( $ ) {
				$(document).ready( function(){

					var submit      = $( 'form' ).find( '.submit' ),
						template    = '<?php echo $template; ?>'
					;

					submit.before( template );

				} );
			} )( jQuery );
		</script>

		<?php
	}

	/**
	 * Returns the default language in the format that MultilingualPress's language select requires (e.g., de_DE).
	 *
	 * @return string
	 */
	private function get_default_language() {

		$default_language = get_site_option( 'WPLANG' );
		$available_languages = get_available_languages();
		if ( in_array( $default_language, $available_languages ) ) {
			return str_replace( '_', '-', $default_language );
		} else {
			return 'en-US';
		}
	}
}
