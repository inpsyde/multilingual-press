<?php # -*- coding: utf-8 -*-

/**
 * Content of the per-site settings tab
 *
 * @version 2014.07.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings_Tab_Content {

	/**
	 * @var Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * @var Mlp_Options_Page_Data
	 */
	private $page_data;

	/**
	 * @var int
	 */
	private $blog_id;

	/**
	 *
	 *
	 * @var Mlp_Site_Relations_Interface
	 */
	private $relations;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param Mlp_Language_Api_Interface   $language_api Language API.
	 * @param Mlp_Options_Page_Data        $page_data    Options page data.
	 * @param int                          $blog_id      Blog ID
	 * @param Mlp_Site_Relations_Interface $relations    Site relations.
	 */
	public function __construct(
		Mlp_Language_Api_Interface $language_api,
		Mlp_Options_Page_Data $page_data,
		$blog_id,
		Mlp_Site_Relations_Interface $relations
	) {

		$this->language_api = $language_api;
		$this->page_data = $page_data;
		$this->blog_id = $blog_id;
		$this->relations = $relations;
	}

	/**
	 * Print tab content and provide two hooks.
	 *
	 * @return void
	 */
	public function render_content() {

		$action = $this->page_data->get_form_action();

		$name = $this->page_data->get_action_name();
		?>
		<form action="<?php echo esc_attr( $action ); ?>" method="post">
			<input type="hidden" name="action" value="<?php echo esc_attr( $name ); ?>" />
			<input type="hidden" name="id" value="<?php echo esc_attr( $this->blog_id ); ?>" />
			<?php
			wp_nonce_field(
				$this->page_data->get_nonce_action(),
				$this->page_data->get_nonce_name()
			);

			$siteoption = get_site_option( 'inpsyde_multilingual', array() );
			$languages  = $this->language_api->get_db()->get_items( array(
				'page' => -1,
			)  );

			echo '<table class="form-table mlp-admin-settings-table">';
			$this->show_language_options( $siteoption, $languages );
			$this->show_blog_relationships( $siteoption );

			/**
			 * Runs at the end of but still inside the site settings table.
			 *
			 * @param int $blog_id Blog ID.
			 */
			do_action( 'mlp_blogs_add_fields', $this->blog_id );

			if ( has_action( 'mlp_blogs_add_fields_secondary' ) ) {
				_doing_it_wrong(
					'mlp_blogs_add_fields_secondary',
					'mlp_blogs_add_fields_secondary is deprecated, use mlp_blogs_add_fields instead.',
					'2.1'
				);
			}
			/**
			 * @see mlp_blogs_add_fields
			 * @deprecated
			 */
			do_action( 'mlp_blogs_add_fields_secondary', $this->blog_id );

			echo '</table>';

			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * @param  array $site_option
	 * @param  array $languages
	 * @return void
	 */
	private function show_language_options( $site_option, $languages ) {

		$selected        = isset( $site_option[ $this->blog_id ]['lang'] ) ? $site_option[ $this->blog_id ]['lang'] : '';
		$blogoption_flag = get_blog_option( $this->blog_id, 'inpsyde_multilingual_flag_url' );

		// Sanitize lang title
		$lang_title = isset( $site_option[ $this->blog_id ]['text'] ) ? stripslashes( $site_option[ $this->blog_id ]['text'] ) : '';
		?>
		<tr class="form-field">
			<th scope="row">
				<label for="inpsyde_multilingual_lang">
					<?php
					esc_html_e( 'Language', 'multilingual-press' );
					?>
				</label>
			</th>
			<td>
				<select name="inpsyde_multilingual_lang" id="inpsyde_multilingual_lang" autocomplete="off">
					<option value="-1"><?php esc_html_e( 'choose language', 'multilingual-press' ); ?></option>
					<?php
					foreach ( $languages as $language ) {

						$language_code = str_replace( '-', '_', $language->http_name );

						// missing HTTP code
						if ( empty( $language_code ) ) {
							continue;
						}
						?>
						<option value="<?php echo esc_attr( $language_code ); ?>"
							<?php selected( $selected, $language_code ); ?>>
							<?php echo esc_html( $this->get_language_name( $language ) ); ?>
						</option>
						<?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row">
				<label for="inpsyde_multilingual_text">
					<?php
					esc_html_e( 'Alternative language title', 'multilingual-press' );
					?>
				</label>
			</th>
			<td>
				<input class="regular-text" type="text" id="inpsyde_multilingual_text" name="inpsyde_multilingual_text"
					value="<?php echo esc_attr( $lang_title ); ?>" />
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
			<th scope="row">
				<label for="inpsyde_multilingual_flag_url">
					<?php
					esc_html_e( 'Flag image URL', 'multilingual-press' );
					?>
				</label>
			</th>
			<td>
				<input
					class="regular-text"
					type="url"
					id="inpsyde_multilingual_flag_url"
					name="inpsyde_multilingual_flag_url"
					value="<?php echo esc_url( $blogoption_flag ); ?>"
					placeholder="http://example.com/flag.png"
				/>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param stdClass $language
	 * @return string
	 */
	private function get_language_name( $language ) {

		$parts = array();

		if ( ! empty( $language->english_name ) ) {
			$parts[] = $language->english_name;
		}

		if ( ! empty( $language->native_name ) ) {
			$parts[] = $language->native_name;
		}

		$parts = array_unique( $parts );

		return join( '/', $parts );
	}

	/**
	 * @param array $site_option
	 * @return void
	 */
	private function show_blog_relationships( $site_option ) {

		if ( ! is_array( $site_option ) ) {
			return;
		}

		unset( $site_option[ $this->blog_id ] );

		if ( empty( $site_option ) ) {
			return;
		}

		?>
		<tr class="form-field">
			<th scope="row"><?php esc_html_e( 'Relationships', 'multilingual-press' ); ?></th>
			<td>
				<?php
				foreach ( $site_option as $blog_id => $meta ) {

					$blog_id = (int) $blog_id;
					// Get blog display name
					switch_to_blog( $blog_id );
					$blog_name = get_bloginfo( 'Name' );
					restore_current_blog();

					// Get current settings
					$related_blogs = $this->relations->get_related_sites( $this->blog_id );
					$checked       = checked( true, in_array( $blog_id, $related_blogs, true ), false );
					$id            = 'related_blog_' . $blog_id;
					?>
					<p>
						<label for="<?php echo esc_attr( $id ); ?>">
							<input id="<?php echo esc_attr( $id ); ?>" <?php echo esc_attr( $checked ); ?>
								type="checkbox" name="related_blogs[]" value="<?php echo esc_attr( $blog_id ); ?>" />
							<?php echo esc_html( $blog_name ); ?>
							-
							<?php echo esc_html( Mlp_Helpers::get_blog_language( $blog_id, false ) ); ?>
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
	}

}
