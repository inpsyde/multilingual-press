<?php
/**
 * Class Mlp_User_Backend_Language
 *
 * Allow users to select an existing language for the entire network back end.
 *
 * @version 2014.03.18
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

	public function setup() {

		// Load user specific language in the backend
		if ( is_admin() )
			add_filter( 'locale', array( $this, 'locale' ) );

		$this->active = $this->module_manager->register(
			array (
				'display_name_callback' => array( $this, 'get_module_title' ),
				'slug'			        => 'class-' . __CLASS__,
				'description_callback'  => array( $this, 'get_module_description' ),
			)
		);

		// Quit here if module is turned off
		if ( ! $this->active )
			return;

		// Add User Field for own blog language
		add_filter( 'personal_options', array ( $this, 'edit_user_profile' ) );
		add_filter( 'profile_update',   array ( $this, 'profile_update' ) );

	}

	/**
	 * Get the description for this feature.
	 *
	 * Used in wp-admin/network/settings.php?page=mlp
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
	 * Used in wp-admin/network/settings.php?page=mlp
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
	 * @param   WP_User $user
	 * @return	void
	 */
	public function edit_user_profile( WP_User $user ) {

		$languages = get_available_languages();

		if ( empty ( $languages ) )
			return;

		$user_language = $this->get_user_language( $user->data->ID );

		// Add English manually, because it won't get added by WordPress itself.
		$languages[] = 'en_US';

		?>
		<tr>
			<th>
				<label for="<?php echo $this->key; ?>"><?php
					esc_html_e( 'Your preferred backend language', 'multilingualpress' );
					?>
				</label>
			</th>
			<td>
				<select name="<?php echo $this->key; ?>" id="<?php echo $this->key; ?>">
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
	 * @param	int $user_id The user id
	 * @return	bool
	 */
	public function profile_update( $user_id ) {

		// Empty means, that the site language is used
		if ( empty ( $_POST[ $this->key ] ) or '' === trim( $_POST[ $this->key ] ) )
			return delete_user_meta( $user_id, $this->key );

		return update_user_meta( $user_id, $this->key, $_POST[ $this->key ] );

	}

	/**
	 * Gets the language which the user set up in his profile.
	 *
	 * @param	int $user_id the id of the user
	 * @param   string $default
	 * @return	string $user_language the users preferred language
	 */
	public function get_user_language( $user_id, $default = '' ) {

		$setting = get_user_meta( $user_id, $this->key, TRUE );

		if ( empty ( $setting ) )
			return $default;

		return $setting;
	}

 	/**
	 * Get the language of the blog in the admin area.
	 *
	 * @wp-hook locale
	 * @param   string $locale
	 * @return	string
	 */
	public function locale( $locale ) {

		if ( ! $this->active )
			return $locale;

		return $this->get_user_language( get_current_user_id(), $locale );
	}

	/**
	 * Show language selector.
	 *
	 * @param array  $lang_files
	 * @param string $current
	 * @return void
	 */
	private function dropdown_languages( $lang_files = array(), $current = '' ) {

		$output = array();

		// Inherit site specific language
		$output[] = '<option value=""' . selected( $current, '', false ) . '>' . __( 'Site Language' ) . "</option>";

		foreach ( (array) $lang_files as $file_name ) {

			$code_lang = basename( $file_name, '.mo' );
			$code_lang = esc_attr( $code_lang );

			if ( 'en_US' === $code_lang )
				$lang = __( 'English' ); // American English
			else
				$lang = format_code_lang( $code_lang );

			$lang          = esc_html ( $lang );
			$selected      = selected( $current, $code_lang, FALSE );
			$output[$lang] = "<option value='$code_lang' $selected>$lang</option>";
		}

		// Order by name
		uksort( $output, 'strnatcasecmp' );

		echo implode( "\n\t", $output );
	}
}

