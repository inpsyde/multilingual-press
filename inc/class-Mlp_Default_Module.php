<?php
/**
 * Default Module
 *
 * @author		fb, rw, ms, th
 * @package		mlp
 * @subpackage	modules
 *
 */

class Mlp_Default_Module extends Multilingual_Press {

	/**
	 * static class object variable
	 *
	 * @static
	 * @access	protected
	 * @var		$class_object NULL
	 * @since	0.1
	 */
	static protected $class_object = NULL;

	/**
	 * array containing language codes and names
	 *
	 * @access	protected
	 * @var		array
	 * @since	0.5
	 */
	protected $lang_codes;

	/**
	 * Load the object and get the current state
	 *
	 * @access public
	 * @since  0.1
	 * @return $class_object
	 */
	public static function get_object() {
		if ( NULL == self::$class_object )
			self::$class_object = new self;
		return self::$class_object;
	}

	/**
	 * init function to register all used hooks and set the Database Table
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	add_filter, get_site_option
	 * @return	void
	 */
	public function __construct() {

		// Handle language codes
		add_filter( 'admin_init', array( $this, 'get_lang_codes' ), 1 );

		// Use this hook to add form fields to the blog options page
		add_filter( 'mlp_blogs_add_fields', array( $this, 'draw_blog_settings_form_fields' ), 1 );

		// Use this hook to handle the user input of your modules' blog settings form fields
		add_filter( 'mlp_blogs_save_fields', array( $this, 'save_blog_settings_form_fields' ) );

		// Add Little Help
		add_filter( 'mlp_options_page_add_metabox', array( $this, 'add_settings_metabox' ), 1 );
	}

	/**
	 * Add meta box to the MLP settingspage
	 *
	 * @access	public
	 * @since	0.6
	 * @uses	add_meta_box
	 * @return	void
	 */
	public function add_settings_metabox() {
		add_meta_box( 'help_metabox', __( 'Multilingual Press Settings', 'multilingualpress' ), array( $this, 'draw_settings_help_tab' ), 'settings_page_mlp', 'normal', 'low', TRUE );
	}

	/**
	 * Add meta box to the MLP settingspage
	 *
	 * @access	public
	 * @since	0.6
	 * @uses	add_meta_box
	 * @return	void
	 */
	public function draw_settings_help_tab() {
		?>
		<p><?php _e( 'In Multilingual Press it is possible to develop additional modules with new setting boxes right under this box. If you want to know how, see our example module on <a href="https://github.com/inpsyde/multilingual-press">GitHub</a>', 'multilingualpress' ); ?></p>
		<?php
	}

	/**
	 * Display the default form fields
	 *
	 * @param   int $current_blog_id | The ID of the current blog
	 * @since	0.5.5b
	 * @uses	get_site_option, _e, plugins_url, selected, plugin_dir_path,
	 * 			esc_html, mlp_get_available_languages, switch_to_blog, get_bloginfo,
	 * 			restore_current_blog, get_blog_option
	 * @return	void
	 */
	public function draw_blog_settings_form_fields( $current_blog_id ) {

		$lang_codes = $this->lang_codes;

		// get registered blogs
		$siteoption = get_site_option( 'inpsyde_multilingual', array() );

		// Get values to display
		// in form fields
		$lang_title = isset( $siteoption[ $current_blog_id ][ 'text' ] ) ? stripslashes( $siteoption[ $current_blog_id ][ 'text' ] ) : '';
		$selected = isset( $siteoption[ $current_blog_id ][ 'lang' ] ) ? $siteoption[ $current_blog_id ][ 'lang' ] : '';
		$blogoption_flag = esc_url( get_blog_option( $current_blog_id, 'inpsyde_multilingual_flag_url' ) );
		?>

		<!-- Language select, alt. language title and flag image url -->

		<div class="postbox">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle"><?php _e( 'Language', 'multilingualpress' ); ?></h3>
			<div class="inside">

				<table class="form-table" id="mlp_blog_language">
					<tr class="form-field">
						<th><?php _e( 'Choose blog language', 'multilingualpress' ) ?></th>
						<td>
							<?php
							if ( ! empty( $lang_codes ) ) {
								?>
								<select name="inpsyde_multilingual_lang" id="inpsyde_multilingual_lang">
									<option value="-1"><?php _e( 'choose language', 'multilingualpress' ); ?></option>
									<option value="en_US" <?php echo selected( 'en_US', $selected ); ?>><?php _e( 'English (US)', 'multilingualpress' ) ?></option>
									<?php
									foreach ( $lang_codes AS $language_code => $language_name ) {

										if ( 5 == strlen( $language_code ) )
											$language_code_flag = strtolower( substr( $language_code, 3, 2 ) );
										else
											$language_code_flag = $language_code;

										if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . '/flags/' . $language_code_flag . '.gif' ) ) {
											?>
											<option value="<?php echo $language_code ?>"<?php echo selected( $selected, $language_code, false ); ?>><?php echo esc_html( $language_name ); ?></option>
											<?php
										}
									}
									?>
								</select>
								<br />

								<span class="description"><?php _e( 'Determine blog language and flag. This will be used in the frontend widget.', 'multilingualpress' ); ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr id="mlp_check_language"><th></th><td></td></tr>
					<tr>
						<th>
							<?php _e( 'Alternative language title', 'multilingualpress' ); ?>
						</th>
						<td>
							<input class="regular-text" type="text" id="inpsyde_multilingual_text" name="inpsyde_multilingual_text" value="<?php echo $lang_title; ?>" />
							<br />
							<span class="description"><?php _e( 'Enter a title here that you want to be displayed in the frontend instead of the default one (i.e. "My English Site")', 'multilingualpress' ); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<?php _e( 'Blog flag image URL', 'multilingualpress' ); ?>
						</th>
						<td>
							<input class="regular-text" type="text" id="inpsyde_multilingual_flag_url" name="inpsyde_multilingual_flag_url" value="<?php echo $blogoption_flag; ?>" />
							<br />
							<span class="description"><?php _e( '(Optional, must begin with http://)', 'multilingualpress' ); ?></span>
						</td>
					</tr>
				</table>

			</div>
		</div>

		<?php
		// Only display this part if there are blogs to interlink
		$a_lang = mlp_get_available_languages( TRUE );
		?>


		<!-- Blog relationships -->
		<?php
		// only display relationship box if an other blog is available
		if ( $siteoption && count( $siteoption ) > 1 || ( count( $siteoption ) == 1 && empty( $siteoption[ $current_blog_id ] ) ) ) { ?>
		<div class="postbox">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle"><?php _e( 'Blog Relationships', 'multilingualpress' ); ?></h3>
			<div class="inside">
				<table class="form-table">
					<tr>
						<th><?php _e( 'Multilingual blog relationships', 'multilingualpress' ) ?></th>
						<td>
							<?php
							foreach ( $siteoption as $blog_id => $meta ) {

								// Filter out current blog
								if ( $current_blog_id === $blog_id )
									continue;

								// Get blog display name
								switch_to_blog( $blog_id );
								$blog_name = get_bloginfo( 'Name' );
								restore_current_blog();

								// Get current settings
								$related_blogs = get_blog_option( $current_blog_id, 'inpsyde_multilingual_blog_relationship' );
								if ( is_array( $related_blogs ) && in_array( $blog_id, $related_blogs ) )
									$selected = 'checked="checked"';
								else
									$selected = '';

								if ( ! isset( $lang_codes[ $meta[ 'lang' ] ] ) )
									$lang_codes[ $meta[ 'lang' ] ] = __( 'English (US)', 'multilingualpress' );

								?>
								<input id="related_blog_<?php echo $blog_id; ?>" <?php echo $selected; ?> type="checkbox" name="related_blogs[]" value="<?php echo $blog_id ?>" /> <?php echo $blog_name; ?> - <?php echo $lang_codes[ $meta[ 'lang' ] ]; ?><br />
								<?php
							}
							?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
		}
	}

	/**
	 * Process the default form fields
	 *
	 * @param   array $data | User input
	 * @since   0.5.5b
	 * @uses	get_site_option, esc_attr, update_site_option,
	 * 			update_blog_option, esc_url
	 * @return	void
	 */
	public function save_blog_settings_form_fields( $data ) {

		$current_blog_id = intval( $data[ 'id' ] );

		// Language and descriptions
		$siteoption = get_site_option( 'inpsyde_multilingual' );

		unset( $siteoption[ $current_blog_id ] );

		if ( ! is_array( $siteoption ) )
			$siteoption = array( );

		foreach ( array ( 'lang' => -1, 'text' => "" ) as $key => $default ) {
			if ( ! empty ( $data[ "inpsyde_multilingual_$key" ] ) )
				$siteoption[ $current_blog_id ][ $key ] = mysql_real_escape_string( $data[ "inpsyde_multilingual_$key" ] );
			else
				$siteoption[ $current_blog_id ][ $key ] = $default;
		}

		update_site_option( 'inpsyde_multilingual', $siteoption );

		// Custom flag URL
		if ( isset( $data[ 'inpsyde_multilingual_flag_url' ] ) )
			update_blog_option( $current_blog_id, 'inpsyde_multilingual_flag_url', esc_url( $data[ 'inpsyde_multilingual_flag_url' ], array( 'http' ) ) );

		// Update blog relationships
		// Get blogs related to the current blog
		$all_blogs = get_site_option( 'inpsyde_multilingual' );
		//$all_blogs = $siteoption;

		if ( ! $all_blogs )
			$all_blogs = array( );

		// The user defined new relationships for this blog. We add it's own ID
		// for internal purposes
		$data[ 'related_blogs' ][] = $current_blog_id;
		$new_rel = $data[ 'related_blogs' ];

		// Loop through related blogs
		foreach ( $all_blogs as $blog_id => $blog_data ) {

			if ( $current_blog_id == $blog_id )
				continue;

			// Check if current blog is valid
			$blog_details = get_blog_details( $blog_id );
			if ( FALSE == $blog_details )
				continue;

			// 1. Get related blogs' current relationships
			$current_rel = get_blog_option( $blog_id, 'inpsyde_multilingual_blog_relationship' );

			if ( ! is_array( $current_rel ) )
				$current_rel = array( );

			// 2. Compare old to new relationships
			// Get the key of the current blog in the relationships array of the looped blog
			$key = array_search( $current_blog_id, $current_rel );

			if ( in_array( $blog_id, $new_rel ) ) {

				// Connect these blogs, if not already.
				if ( FALSE === $key )
					$current_rel[ ] = $current_blog_id;
			} else {
				// These blogs should not be connected. Delete
				// possibly existing connection
				if ( FALSE !== $key && ISSET( $current_rel[ $key ] ) )
					unset( $current_rel[ $key ] );
			}

			// $current_rel should be our relationships array for the currently looped blog
			update_blog_option( $blog_id, 'inpsyde_multilingual_blog_relationship', $current_rel );
		}

		// Save Blog-Relationship
		// @TODO: validate user input
		update_blog_option( $current_blog_id, 'inpsyde_multilingual_blog_relationship', $new_rel );
	}

	/**
	 * Add meta box
	 *
	 * @since   0.5.5b
	 * @uses	add_meta_box
	 * @return	void
	 */
	public function add_metabox() {
		add_meta_box( 'demo_metabox', __( 'Demo Module Metabox', 'multilingualpress' ), array( $this, 'draw_options_page_form_fields' ), Mlp_Settingspage::$class_object->options_page, 'normal', 'low', TRUE );
	}

	/**
	 * This is the callback of the metabox
	 * used to display the modules options page
	 * form fields
	 *
	 * @since   0.5.5b
	 * @uses	get_site_option
	 * @return	void
	 */
	public function draw_options_page_form_fields() {

		$options = get_site_option( 'inpsyde_multilingual_default_module-module' );
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th>
						<?php _e( 'Example 1', 'multilingualpress' ); ?>
					</th>
					<td>
						<input type="checkbox" <?php echo ( ( TRUE == $options[ 'mlp_default_module1' ] ) ? 'checked="checked"' : '' ); ?> id="mlp_default_module" value="true" name="mlp_default_module1" />
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Example 2', 'multilingualpress' ); ?>
					</th>
					<td>
						<input type="checkbox" <?php echo ( ( TRUE == $options[ 'mlp_default_module2' ] ) ? 'checked="checked"' : '' ); ?> id="mlp_default_module" value="true" name="mlp_default_module2" />
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Hook into mlp_settings_save_fields to
	 * handle module user input
	 *
	 * @since   0.5.5b
	 * @uses	get_site_option, update_site_option
	 * @return	void
	 */
	public function save_options_page_form_fields() {

		// Get current site options
		$options = get_site_option( 'inpsyde_multilingual_default_module-module' );

		// Get values from submitted form
		$options[ 'mlp_default_module1' ] = ( ISSET( $_POST[ 'mlp_default_module1' ] ) ) ? TRUE : FALSE;
		$options[ 'mlp_default_module2' ] = ( ISSET( $_POST[ 'mlp_default_module2' ] ) ) ? TRUE : FALSE;

		update_site_option( 'inpsyde_multilingual_default_module-module', $options );
	}

	/**
	 * save all language codes from wordpress
	 * and update them with the language codes
	 * used in the language file repository
	 *
	 * @access  public
	 * @param   array $lang_codes | languages from wordpress
	 * @since   0.1
	 * @uses	apply_filters
	 * @return  array $lang_codes | List of updated lang codes
	 */
	public function load_lang_codes( $lang_codes ) {

		$obsolete_shortcodes = array(
			'fr', 'es', 'bg', 'it', 'da', 'de', 'gl', 'hu', 'is', 'id', 'ky', 'mg', 'mk', 'ml', 'en',
			'bs', 'ne', 'no', 'pa', 'pl', 'pt', 'ro', 'ru', 'sa', 'sd', 'si', 'sk', 'sl', 'so', 'sr', 'sv',
			'tr', 'ug', 'uz', 'bn', 'cs', 'ms', 'my'
		);
		foreach ( $obsolete_shortcodes AS $os )
			unset( $lang_codes[ $os ] );

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
		$lang_codes[ 'en' ] =		__( 'English (Great Britain)', 'multilingualpress' );
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

		// Sort them according to
		// language name
		asort( $lang_codes );

		// Modules can hook in here
		// to add or modify codes
		$lang_codes = apply_filters( 'mlp_language_codes', $lang_codes );

		// Make the codes available in the whole class
		// @TODO: & parent class??
		$this->lang_codes = $lang_codes;
		parent::$class_object->lang_codes = $lang_codes;

		return $lang_codes;
	}

	/**
	 * add filter to get the language
	 * shortcodes from wordpress
	 *
	 * @access  public
	 * @since   0.1
	 * @uses	add_filter, format_code_lang, remove_filter
	 * @return  array $lang_codes
	 */
	public function get_lang_codes() {

		// Get Current Language Codes
		add_filter( 'lang_codes', array( $this, 'load_lang_codes' ) );
		format_code_lang( '' ); // hack to get all available languages
		remove_filter( 'lang_codes', array( $this, 'load_lang_codes' ) );
	}

}

Mlp_Default_Module::get_object();