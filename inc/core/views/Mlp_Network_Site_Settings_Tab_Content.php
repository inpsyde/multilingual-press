<?php # -*- coding: utf-8 -*-
class Mlp_Network_Site_Settings_Tab_Content {

	private $language_api;
	private $page_data;
	private $blog_id;

	/**
	 * Constructor.
	 */
	public function __construct(
		Mlp_Language_Api_Interface $language_api,
		Mlp_Options_Page_Data      $page_data,
		                           $blog_id
		) {
		$this->language_api = $language_api;
		$this->page_data    = $page_data;
		$this->blog_id      = $blog_id;
	}

	public function render_content() {
		?>
		<form action="<?php print $this->page_data->get_form_action(); ?>" method="post">
			<input type="hidden" name="action" value="<?php echo $this->page_data->get_action_name(); ?>" />
			<input type="hidden" name="id" value="<?php echo $this->blog_id; ?>" />
			<?php
			wp_nonce_field(
				$this->page_data->get_nonce_action(),
				$this->page_data->get_nonce_name()
			);

			$siteoption = get_site_option( 'inpsyde_multilingual', array() );
			$languages  = $this->language_api->get_db()->get_items( -1 );

			print '<table class="form-table mlp-admin-settings-table">';
			$this->show_language_options( $siteoption, $languages );
			$this->show_blog_relationships( $siteoption, $languages );

			// HTTP redirect is called here
			do_action( 'mlp_blogs_add_fields', $this->blog_id );
			// back compat
			do_action( 'mlp_blogs_add_fields_secondary', $this->blog_id );

			print '</table>';

			submit_button();
			?>
		</form>
		<?php
	}

	private function show_language_options( $siteoption, $languages ) {

		// Custom names are now set in the Language Manager
		//$lang_title = isset( $siteoption[ $this->blog_id ][ 'text' ] ) ? stripslashes( $siteoption[ $this->blog_id ][ 'text' ] ) : '';
		$selected = isset( $siteoption[ $this->blog_id ][ 'lang' ] ) ? $siteoption[ $this->blog_id ][ 'lang' ] : '';
		$blogoption_flag = esc_url( get_blog_option( $this->blog_id, 'inpsyde_multilingual_flag_url' ) );

		// Sanitize lang title
		$lang_title = isset( $siteoption[ $this->blog_id ][ 'text' ] ) ? stripslashes( $siteoption[ $this->blog_id ][ 'text' ] ) : '';
		?>
		<tr>
			<td style="width:10em">
				<label for="inpsyde_multilingual_lang">
				<?php
				esc_html_e( 'Language', 'multilingualpress' );
				?>
				</label>
			</td>
			<td>
				<select name="inpsyde_multilingual_lang" id="inpsyde_multilingual_lang">
					<option value="-1"><?php esc_html_e( 'choose language', 'multilingualpress' ); ?></option>
					<?php
					foreach ( $languages as $language ) {

						$language_code = esc_attr( str_replace( '-', '_', $language->http_name ) );

						// missing HTTP code
						if ( empty ( $language_code ) )
							continue;

						$language_name = esc_html( $this->get_language_name( $language ) );
						$select        = selected( $selected, $language_code, FALSE );
						print "<option value='$language_code' $select>$language_name</option>";
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="inpsyde_multilingual_text">
					<?php
					esc_html_e( 'Alternative language title', 'multilingualpress' );
					?>
				</label>
			</td>
			<td>
				<input class="regular-text" type="text" id="inpsyde_multilingual_text" name="inpsyde_multilingual_text" value="<?php echo $lang_title; ?>" />
				<br />
				<span class="description"><?php esc_html_e( 'Enter a title here that you want to be displayed in the frontend instead of the default one (i.e. "My English Site")', 'multilingualpress' ); ?></span>
			</td>
		</tr>
		<tr>
			<td>
				<label for="inpsyde_multilingual_flag_url">
				<?php
				esc_html_e( 'Flag image URL', 'multilingualpress' );
				?>
				</label>
			</td>
			<td>
				<input
					class="regular-text"
					type="url"
					id="inpsyde_multilingual_flag_url"
					name="inpsyde_multilingual_flag_url"
					value="<?php echo $blogoption_flag; ?>"
					placeholder="http://example.com/flag.png"
				/>
			</td>
		</tr>
		<?php
	}

	private function get_language_name( $language ) {

		$parts = array();

		if ( ! empty ( $language->english_name ) )
			$parts[] = $language->english_name;

		if ( ! empty ( $language->native_name ) )
			$parts[] = $language->native_name;

		$parts = array_unique( $parts );

		return join( '/', $parts );
	}

	private function show_blog_relationships( $siteoption, $lang_codes ) {

		if ( ! is_array( $siteoption ) )
			return;

		unset ( $siteoption[ $this->blog_id ] );

		//*
		if ( empty ( $siteoption ) )
			return;
			/**/

		//print '<pre>$siteoption = ' . esc_html( var_export( $siteoption, TRUE ) ) . '</pre>';

		?>
		<tr>
			<td><?php esc_html_e( 'Relationships', 'multilingualpress' ); ?></td>
			<td>
		<?php
		foreach ( $siteoption as $blog_id => $meta ) {

			$blog_id = (int) $blog_id;
			// Get blog display name
			switch_to_blog( $blog_id );
			$blog_name = esc_html( get_bloginfo( 'Name' ) );
			restore_current_blog();

			// Get current settings
			$related_blogs = (array) get_blog_option( $this->blog_id, 'inpsyde_multilingual_blog_relationship' );
			$checked       = checked( TRUE, in_array( $blog_id, $related_blogs ), FALSE );
			$id            = "related_blog_$blog_id";
			//print '<pre>$related_blogs = ' . esc_html( var_export( $related_blogs, TRUE ) ) . '</pre>';
			?>
			<p>
				<label for="<?php echo $id; ?>">
					<input id="<?php echo $id; ?>" <?php echo $checked; ?> type="checkbox" name="related_blogs[]" value="<?php echo $blog_id ?>" />
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
			'multilingualpress'
		);
		?>
		</p>
			</td>
		</tr>
		<?php
	}
	public function load_lang_codes() {

		$langs = $this->lang_table->get_languages();

		$langs_formatted = array();

		foreach ( $langs as $lang )
			$langs_formatted[ $lang->key ] = $lang->name;

		return $langs_formatted;
		/*
		print '<pre>$langs = ' . esc_html( var_export( $langs, TRUE ) ) . '</pre>';
		print '<pre>$langs_formatted = ' . esc_html( var_export( $langs_formatted, TRUE ) ) . '</pre>';
		*/

		/*
		$lang_codes = array ();

		$lang_codes[ 'fa_IR' ] =	__( 'Persian', 'multilingualpress' );
		$lang_codes[ 'zh_TW' ] =	__( 'Simplified Chinese (Taiwan)', 'multilingualpress' );
		$lang_codes[ 'zh_HK' ] =	__( 'Simplified Chinese (Hong Kong)', 'multilingualpress' );
		$lang_codes[ 'zh_CN' ] =	__( 'Simplified Chinese (China)', 'multilingualpress' );
		$lang_codes[ 'ta_LK' ] =	__( 'Tamil (Sri Lanka)', 'multilingualpress' );
		$lang_codes[ 'ta_IN' ] =	__( 'Tamil (India)', 'multilingualpress' );
		$lang_codes[ 'ru_UA' ] =	__( 'Russian (Ukraine)', 'multilingualpress' );
		$lang_codes[ 'my_MM' ] =	__( 'Burmese', 'multilingualpress' );
		$lang_codes[ 'ms_MY' ] =	__( 'Malay', 'multilingualpress' );
		$lang_codes[ 'hi_IN' ] =	__( 'Hindi', 'multilingualpress' );
		$lang_codes[ 'he_IL' ] =	__( 'Hebrew', 'multilingualpress' );
		$lang_codes[ 'haw_US' ] =	__( 'Hawaiian', 'multilingualpress' );
		$lang_codes[ 'cs_CZ' ] =	__( 'Czech', 'multilingualpress' );
		$lang_codes[ 'bn_BD' ] =	__( 'Bengali', 'multilingualpress' );
		$lang_codes[ 'uz_UZ' ] =	__( 'Uzbek', 'multilingualpress' );
		$lang_codes[ 'ug_CN' ] =	__( 'Uighur; Uyghur', 'multilingualpress' );
		$lang_codes[ 'tr_TR' ] =	__( 'Turkish', 'multilingualpress' );
		$lang_codes[ 'sv_SE' ] =	__( 'Swedish', 'multilingualpress' );
		$lang_codes[ 'sr_RS' ] =	__( 'Serbian', 'multilingualpress' );
		$lang_codes[ 'so_SO' ] =	__( 'Somali', 'multilingualpress' );
		$lang_codes[ 'sl_SI' ] =	__( 'Slovenian', 'multilingualpress' );
		$lang_codes[ 'sk_SK' ] =	__( 'Slowak', 'multilingualpress' );
		$lang_codes[ 'si_LK' ] =	__( 'Sinhala; Sinhalese', 'multilingualpress' );
		$lang_codes[ 'fr_FR' ] =	__( 'French (France)', 'multilingualpress' );
		$lang_codes[ 'fr_BE' ] =	__( 'French (Belgium)', 'multilingualpress' );
		$lang_codes[ 'es_CL' ] =	__( 'Spanish (Chile)', 'multilingualpress' );
		$lang_codes[ 'es_ES' ] =	__( 'Spanish (Castilian)', 'multilingualpress' );
		$lang_codes[ 'es_PE' ] =	__( 'Spanish (Peru)', 'multilingualpress' );
		$lang_codes[ 'es_VE' ] =	__( 'Spanish (Venezuela)', 'multilingualpress' );
		$lang_codes[ 'az_TR' ] =	__( 'Azerbaijani (Turkey)', 'multilingualpress' );
		$lang_codes[ 'bg_BG' ] =	__( 'Bulgarian', 'multilingualpress' );
		$lang_codes[ 'it_IT' ] =	__( 'Italian', 'multilingualpress' );
		$lang_codes[ 'da_DK' ] =	__( 'Danish', 'multilingualpress' );
		$lang_codes[ 'de_DE' ] =	__( 'German', 'multilingualpress' );
		$lang_codes[ 'en_CA' ] =	__( 'English (Canada)', 'multilingualpress' );
		$lang_codes[ 'gl_ES' ] =	__( 'Galician', 'multilingualpress' );
		$lang_codes[ 'gu' ]	=		__( 'Gujarati', 'multilingualpress' );
		$lang_codes[ 'hu_HU' ] =	__( 'Hungarian', 'multilingualpress' );
		$lang_codes[ 'is_IS' ] =	__( 'Icelandic', 'multilingualpress' );
		$lang_codes[ 'id_ID' ] =	__( 'Indonesian', 'multilingualpress' );
		$lang_codes[ 'jv_ID' ] =	__( 'Indonesian (Java)', 'multilingualpress' );
		$lang_codes[ 'ko_KR' ] =	__( 'Kanuri', 'multilingualpress' );
		$lang_codes[ 'ky_KY' ] =	__( 'Kirghiz; Kyrgyz', 'multilingualpress' );
		$lang_codes[ 'mg_MG' ] =	__( 'Malagasy', 'multilingualpress' );
		$lang_codes[ 'mk_MK' ] =	__( 'Macedonian', 'multilingualpress' );
		$lang_codes[ 'ml_IN' ] =	__( 'Malayalam', 'multilingualpress' );
		$lang_codes[ 'en_GB' ] =	__( 'English (Great Britain)', 'multilingualpress' );
		$lang_codes[ 'en_US' ] =	__( 'English (USA)', 'multilingualpress' );
		$lang_codes[ 'bs_BA' ] =	__( 'Bosnian', 'multilingualpress' );
		$lang_codes[ 'ne_NP' ] =	__( 'Nepali', 'multilingualpress' );
		$lang_codes[ 'nl_BE' ] =	__( 'Dutch (Belgium)', 'multilingualpress' );
		$lang_codes[ 'nb_NO' ] =	__( 'Bokmål', 'multilingualpress' );
		$lang_codes[ 'nn_NO' ] =	__( 'Nynorsk', 'multilingualpress' );
		$lang_codes[ 'pa_IN' ] =	__( 'Panjabi; Punjabi', 'multilingualpress' );
		$lang_codes[ 'pl_PL' ] =	__( 'Polish', 'multilingualpress' );
		$lang_codes[ 'pt_PT' ] =	__( 'Portuguese (Portugal)', 'multilingualpress' );
		$lang_codes[ 'pt_BR' ] =	__( 'Portuguese (Brasil)', 'multilingualpress' );
		$lang_codes[ 'ro_RO' ] =	__( 'Romanian', 'multilingualpress' );
		$lang_codes[ 'ru_RU' ] =	__( 'Russian', 'multilingualpress' );
		$lang_codes[ 'sa_IN' ] =	__( 'Sanskrit', 'multilingualpress' );
		$lang_codes[ 'sd_PK' ] =	__( 'Sindhi', 'multilingualpress' );
		$lang_codes[ 'kr' ] =	__( 'Korean (Johab)', 'multilingualpress' );
*/
		// Sort them according to
		// language name
		asort( $lang_codes );

		// Modules can hook in here
		// to add or modify codes
		return apply_filters( 'mlp_language_codes', $lang_codes );
	}
}