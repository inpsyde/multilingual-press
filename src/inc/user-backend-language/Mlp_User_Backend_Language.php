<?php # -*- coding: utf-8 -*-

/**
 * Class Mlp_User_Backend_Language
 *
 * Allow users to select an existing language for the entire network backend.
 *
 * @version 2015.03.05
 * @author  Inpsyde GmbH
 * @license GPL
 */
class Mlp_User_Backend_Language {

	/**
	 * $var Mlp_Module_Manager_Interface
	 */
	private $module_manager;

	/**
	 * Internal identifier.
	 *
	 * @var string
	 */
	private $key = 'mlp_user_language';

	/**
	 * Feature activation status.
	 *
	 * @var bool
	 */
	private $active = FALSE;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Module_Manager_Interface $module_manager
	 */
	public function __construct( Mlp_Module_Manager_Interface $module_manager ) {

		$this->module_manager = $module_manager;
	}

	/**
	 * Set up filters, when active.
	 *
	 * @return void
	 */
	public function setup() {

		// Load user specific language in the backend
		add_filter( 'locale', array( $this, 'locale' ) );

		$this->active = $this->module_manager->register(
			array(
				'display_name_callback' => array( $this, 'get_module_title' ),
				'slug'                  => 'class-' . __CLASS__,
				'description_callback'  => array( $this, 'get_module_description' ),
			)
		);

		// Quit here if module is turned off
		if ( ! $this->active ) {
			return;
		}

		// Add User Field for own blog language
		add_filter( 'personal_options', array( $this, 'edit_user_profile' ) );
		add_filter( 'profile_update', array( $this, 'profile_update' ) );

		add_action( 'admin_footer-options-general.php', array( $this, 'set_selected_language' ) );
	}

	/**
	 * Get the description for this feature.
	 *
	 * Used in wp-admin/network/settings.php?page=mlp.
	 *
	 * @return string
	 */
	public function get_module_description() {

		return __(
			'Let each user choose a preferred language for the backend of all connected sites. Does not affect the frontend.',
			'multilingualpress'
		);
	}

	/**
	 * Get the title for this feature.
	 *
	 * Used in wp-admin/network/settings.php?page=mlp.
	 *
	 * @return string
	 */
	public function get_module_title() {

		return __( 'User Backend Language', 'multilingualpress' );
	}

	/**
	 * Display user meta.
	 *
	 * @wp-hook personal_options
	 *
	 * @param WP_User $user User object.
	 *
	 * @return void
	 */
	public function edit_user_profile( WP_User $user ) {

		$languages = get_available_languages();

		if ( ! $languages ) {
			return;
		}

		// Add English manually, because it won't get added by WordPress itself.
		$languages[ ] = 'en_US';

		$user_language = $this->get_user_language( $user->ID );
		?>
		<tr>
			<th>
				<label for="<?php echo $this->key; ?>">
					<?php esc_html_e( 'Your preferred backend language', 'multilingualpress' ); ?>
				</label>
			</th>
			<td>
				<select name="<?php echo $this->key; ?>" id="<?php echo $this->key; ?>" autocomplete="off">
					<?php $this->dropdown_languages( $languages, $user_language ); ?>
				</select>
			</td>
		</tr>
	<?php
	}

	/**
	 * Save the user meta.
	 *
	 * @wp-hook profile_update
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return bool
	 */
	public function profile_update( $user_id ) {

		// Empty means, that the site language is used
		if ( empty( $_POST[ $this->key ] ) or '' === trim( $_POST[ $this->key ] ) ) {
			return delete_user_meta( $user_id, $this->key );
		}

		return update_user_meta( $user_id, $this->key, $_POST[ $this->key ] );
	}

	/**
	 * Gets the language which the user set up in his profile.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $default Optional. Default language. Defaults to ''.
	 *
	 * @return string $user_language The user's preferred language.
	 */
	public function get_user_language( $user_id, $default = '' ) {

		$setting = get_user_meta( $user_id, $this->key, TRUE );

		if ( empty( $setting ) ) {
			return $default;
		}

		return $setting;
	}

	/**
	 * Get the language of the blog in the admin area.
	 *
	 * @wp-hook locale
	 *
	 * @param string $locale
	 *
	 * @return string
	 */
	public function locale( $locale ) {

		if ( ! $this->active ) {
			return $locale;
		}

		$current_user_id = get_current_user_id();

		return $this->get_user_language( $current_user_id, $locale );
	}

	/**
	 * Show language selector.
	 *
	 * @param array  $lang_files Optional. Language file names. Defaults to array().
	 * @param string $current    Optional. Current language code. Defaults to ''.
	 *
	 * @return void
	 */
	private function dropdown_languages( array $lang_files = array(), $current = '' ) {

		$output = array();

		// Inherit site specific language
		$output[ ] = '<option value=""' . selected( $current, '', FALSE ) . '>'
			. __( 'Site Language', 'multilingualpress' ) . "</option>";

		foreach ( (array) $lang_files as $file_name ) {
			$code_lang = basename( $file_name, '.mo' );
			$code_lang = esc_attr( $code_lang );

			if ( 'en_US' === $code_lang ) {
				// American English
				$lang = __( 'English', 'multilingualpress' );
			} else {
				$lang = format_code_lang( $code_lang );
			}

			$lang = esc_html( $lang );

			$selected = selected( $code_lang, $current, FALSE );
			if ( '' !== $selected ) {
				$selected = ' ' . $selected;
			}

			$output[ $lang ] = '<option value="' . $code_lang . '"' . $selected . '>' . $lang . '</option>';
		}

		// Order by name
		uksort( $output, 'strnatcasecmp' );

		echo implode( "\n\t", $output );
	}

	/**
	 * Set the site language to what it actually is (i.e., not the user backend language).
	 *
	 * @wp-hook admin_footer-options-general.php
	 *
	 * @return void
	 */
	public function set_selected_language() {

		unset ( $GLOBALS['locale'] );

		$filtered_locale = get_locale();

		remove_filter( 'locale', array( $this, 'locale' ) );

		$unfiltered_locale = get_locale();

		if ( $filtered_locale !== $unfiltered_locale ) {
			$this->print_script( $unfiltered_locale );
		}

		add_filter( 'locale', array( $this, 'locale' ) );
	}

	/**
	 * Print the script to fix the visible value for the site language
	 *
	 * @link   https://github.com/inpsyde/multilingual-press/issues/89
	 * @param  string $value
	 * @return void
	 */
	private function print_script( $value ) {

		if ( 'en_US' === $value ) {
			$value = '';
		}
		else {
			$value = esc_js( $value );
		}

		?>
		<script>
			document.getElementById( 'WPLANG' ).value = '<?php echo $value; ?>';
		</script>
		<?php
	}

}
