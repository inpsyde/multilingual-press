<?php # -*- coding: utf-8 -*-

/**
 * This view template renders several setting fields for the Add New Site admin page.
 */
class Mlp_New_Site_View {

	/**
	 * @var string
	 */
	private $default_language;

	/**
	 * @var Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Language_Api_Interface $language_api
	 */
	public function __construct( Mlp_Language_Api_Interface $language_api ) {

		$this->language_api = $language_api;

		$this->default_language = $this->get_default_language();
	}

	/**
	 * Prints the template for the MultilingualPress table, and fires an action to inject markup.
	 *
	 * @wp-hook admin_footer
	 *
	 * @return void
	 */
	public function print_template() {

		global $hook_suffix;

		if ( 'site-new.php' !== $hook_suffix ) {
			return;
		}

		$db = $this->language_api->get_db();

		$languages = $db->get_items( array( 'page' => - 1 ) );
		?>
		<script type="text/html" id="mlp-add-new-site-template">
			<h3>
				<?php esc_html_e( 'MultilingualPress', 'multilingual-press' ); ?>
			</h3>
			<table class="form-table">
				<tr class="form-field">
					<td>
						<label for="mlp-site-language">
							<?php esc_html_e( 'Language', 'multilingual-press' ); ?>
						</label>
					</td>
					<td>
						<select name="inpsyde_multilingual_lang" id="mlp-site-language" autocomplete="off">
							<option value="-1">
								<?php esc_html_e( 'Choose language', 'multilingual-press' ); ?>
							</option>
							<?php foreach ( $languages as $language ) : ?>
								<?php $this->render_language_option( $language ); ?>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr class="form-field">
					<td>
						<label for="inpsyde_multilingual_text">
							<?php esc_html_e( 'Alternative language title', 'multilingual-press' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="inpsyde_multilingual_text" class="regular-text"
							id="inpsyde_multilingual_text">
						<p class="description">
							<?php
							esc_html_e(
								'Enter a title here that you want to be displayed in the frontend instead of the default one (i.e. "My English Site")',
								'multilingual-press'
							);
							?>
						</p>
					</td>
				</tr>
				<tr class="form-field">
					<td>
						<label for="inpsyde_multilingual_text">
							<?php esc_html_e( 'Relationships', 'multilingual-press' ); ?>
						</label>
					</td>
					<td>
						<?php $this->render_relationships(); ?>
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

		if ( ! in_array( $default_language, $available_languages ) ) {
			return 'en-US';
		}

		return str_replace( '_', '-', $default_language );
	}

	/**
	 * Renders the language option element for the given language object.
	 *
	 * @param object $language Language object.
	 *
	 * @return void
	 */
	private function render_language_option( $language ) {

		if ( empty( $language->http_name ) ) {
			return;
		}

		$selected = selected( $this->default_language, $language->http_name, false );
		?>
		<option value="<?php echo esc_attr( $language->http_name ); ?>" <?php echo $selected; ?>>
			<?php echo "{$language->english_name}/{$language->native_name}"; ?>
		</option>
		<?php
	}

	/**
	 * Renders the relationship settings.
	 *
	 * @return void
	 */
	private function render_relationships() {

		$sites = get_site_option( 'inpsyde_multilingual', array() );
		foreach ( array_keys( $sites ) as $site_id ) {
			$site_id = (int) $site_id;

			$id = "related_blog_$site_id";

			switch_to_blog( $site_id );
			$blog_name = get_bloginfo( 'name' );
			restore_current_blog();
			?>
			<p>
				<label for="<?php echo $id; ?>">
					<input type="checkbox" name="related_blogs[]" value="<?php echo $site_id ?>"
						id="<?php echo $id; ?>">
					<?php echo esc_html( $blog_name ) . '-' . Mlp_Helpers::get_blog_language( $site_id ); ?>
				</label>
			</p>
			<?php
		}
	}
}
