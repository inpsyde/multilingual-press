<?php
/**
 * Module Name:	Multilingual Press Demo Module
 * Author:		Inpsyde GmbH
 * Version:		0.2
 * Author URI:	http://inpsyde.com
 *
 * Changelog
 *
 * 0.1
 * - Initial Commit
 */

class Multilingual_Press_Demo_Module extends Multilingual_Press {

	/**
	 * Instance holder
	 *
	 * @since	0.1
	 * @static
	 * @access	protected
	 * @var		NULL | Multilingual_Press_Demo_Module
	 */
	static protected $class_object = NULL;

	/**
	 * Load the object and get the current state
	 *
	 * @since	0.1
	 * @access	public
	 * @return	$class_object
	 */
	public static function get_object() {

		if ( NULL == self::$class_object )
			self::$class_object = new self;
		return self::$class_object;
	}

	/**
	 * init function to register all used hooks and set the Database Table
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	add_filter
	 * @return	void
	 */
	public function __construct() {

		// Quit here if module is turned off
		if ( FALSE === $this->module_init() )
			return;

		// Use this hook to add a meta box to the networks options page
		add_filter( 'mlp_options_page_add_metabox', array( $this, 'add_settings_metabox' ), 1 );
		// Use this hook to handle the user input of your modules' options page form fields
		add_filter( 'mlp_settings_save_fields', array( $this, 'save_options_page_form_fields' ) );
	}

	/**
	 * Add meta box to the MLP settingspage
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	add_meta_box, __
	 * @return	void
	 */
	public function add_settings_metabox() {

		add_meta_box( 'demo_module_settings_metabox', __( 'Demo Module', 'multilingualpress' ), array( $this, 'draw_options_page_form_fields' ), 'settings_page_mlp', 'normal', 'low', TRUE );
	}

	/**
	 * This is the callback of the metabox
	 * used to display the modules options page
	 * form fields
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	get_site_option, _e
	 * @return	void
	 */
	public function draw_options_page_form_fields() {

		// Get settings field
		$options = get_site_option( 'inpsyde_multilingual_press_my_setting' );
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="demo_module[my_setting]"><?php _e( 'Label of my setting', 'multilingualpress' ); ?></label></th>
					<td><input type="text" class="large-text" name="demo_module[my_setting]" id="demo_module[my_setting]" value="<?php echo $options[ 'my_setting' ]; ?>" /></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Hook into mlp_settings_save_fields to
	 * handle module user input
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	get_site_option, update_site_option
	 * @return	void
	 */
	public function save_options_page_form_fields() {

		// Save the posted Data into a settings field
		update_site_option( 'inpsyde_multilingual_press_my_setting', $_POST[ 'demo_module' ] );
	}

	/**
	 * Determine the current module state (on/off).
	 * NOTE: the module file must correspond
	 * with the 'slug' provided, i.e. cpt-module.php
	 * must have 'cpt-module' as a slug parameter.
	 *
	 * @access	private
	 * @since	0.1
	 * @return	FALSE | if turned off
	 */
	private function module_init() {

		// Check module state
		$module_init = array(
			'display_name'	=> 'Demo Module',
			'slug'			=> 'class-' . __CLASS__
		);

		if ( 'off' === parent::get_module_state( $module_init ) )
			return FALSE;
	}

	/**
	 * return the module description
	 *
	 * @access	public
	 * @since	0.2
	 * @uses	__
	 * @return	string
	 */
	public function get_module_description() {

		return __( 'This Demo Module shows developer how to use our plugin.', 'multilingualpress' );
	}
}

if ( function_exists( 'add_filter' ) )
	Multilingual_Press_Demo_Module::get_object();
?>