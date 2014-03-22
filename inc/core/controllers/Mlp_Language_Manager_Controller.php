<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Language_Manager_Controller
 *
 * Control settings page for Language Manager table.
 *
 * @version 2014.01.15
 * @author  Inpsude GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Manager_Controller implements Mlp_Updatable {

	private $plugin_data;

	private $db;
	private $page_data;
	private $view;

	private $page_title;
	private $pagination_data;
	private $pagination_view;

	private $reset_action = 'mlp_reset_language_table';

	/**
	 * Constructor.
	 *
	 * @param Inpsyde_Property_List_Interface $data
	 * @param Mlp_Data_Access $db
	 */
	public function __construct(
		Inpsyde_Property_List_Interface $data,
		Mlp_Data_Access                 $db
		) {

		$this->plugin_data     = $data;
		$this->page_title      = __( 'Language Manager', 'multilingualpress' );
		$this->db              = $db;
		$this->pagination_data = new Mlp_Language_Manager_Pagination_Data( $db );
		$this->page_data       = new Mlp_Language_Manager_Options_Page_Data( $this->page_title );
		$this->view            = new Mlp_Language_Manager_Page_View(
			$this->page_data,
			$this,
			$this->pagination_data
			);

		$updater = new Mlp_Language_Updater(
			$this->page_data,
			$this->pagination_data,
			new Mlp_Array_Diff( $this->get_columns() ),
			$db
		);

		add_action(
			'admin_post_mlp_update_languages',
			array ( $updater, 'update_languages' )
		);
		add_action(
			'network_admin_menu',
			array( $this, 'register_page' ), 50
		);
		add_action(
			"admin_post_{$this->reset_action}",
			array ( $this, 'reset_table' )
		);
	}

	public function register_page() {

		$id = add_submenu_page(
			'settings.php',
			$this->page_title,
			$this->page_title,
			'manage_network_options',
			'language-manager',
			array( $this->view, 'render' )
		);

		// for JavaScript use "admin_print_scripts-$id"
		add_action( "admin_print_styles-$id", array( $this, 'enqueue_style' ) );
	}

	public function enqueue_style() {
		wp_enqueue_style(
			'language_manager',
			$this->plugin_data->css_url . 'language-manager.css'
		);
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function update( $name ) {

		if ( 'before_form' === $name ) {
			$this->before_form();
			return;
		}

		if ( 'before_table' === $name ) {
			$this->before_table();
			return;
		}

		if ( 'show_table' === $name ) {
			$this->show_table();
			return;
		}

		if ( 'after_table' === $name ) {
			$this->after_table();
			return;
		}

		if ( 'after_form' === $name )
			$this->after_form();
	}

	private function get_reset_table_link() {

		$request = remove_query_arg( 'msg', wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$nonce   = wp_create_nonce( $this->page_data->get_nonce_action() );
		$url     = add_query_arg(
			array (
				'action'                           => $this->reset_action,
				$this->page_data->get_nonce_name() => $nonce,
				'_wp_http_referer'                 => esc_attr( $request )
			),
			$this->page_data->get_form_action()
		);
		// there is no general class for delete links. this is not ideal.
		print "<p><a href='$url' class='delete submitdelete' style='color:red'>"
			. esc_html__( 'Reset table to default values', 'multilingualpress' )
			. '</a></p>';
	}

	public function reset_table() {

		check_admin_referer(
			$this->page_data->get_nonce_action(),
			$this->page_data->get_nonce_name()
		);

		$table_name = new Mlp_Db_Languages_Schema;
		$installer = new Mlp_Db_Installer( $table_name );
		$installer->uninstall();
		$installer->install();

		do_action( 'mlp_reset_table_done', $table_name );

		$url = add_query_arg( 'msg', 'resettable', $_REQUEST[ '_wp_http_referer' ] );
		wp_safe_redirect( $url );
		exit;
	}

	private function before_form() {

		if ( ! empty ( $_GET[ 'msg' ] ) )
			print $this->get_update_message();
	}

	/**
	 * Get message text for success notice.
	 *
	 * @return string
	 */
	private function get_update_message() {

		$type  = strtok( $_GET[ 'msg' ], '-' );
		$num   = (int) strtok( '-' );
		$num_f = number_format_i18n( $num );
		$text  = '';

		if ( 'updated' === $type ) {
			$text = sprintf(
				_n(
					'One language changed.',
					'%s languages changed.',
					$num,
					'multilingualpress'
				),
				$num_f
			);
		}
		if ( 'resettable' === $type ) {
			$text = esc_html__(
				'Table reset to default values.',
				'multilingualpress'
			);
		}

		if ( '' === $text )
			return '';

		return '<div class="updated"><p>' . esc_html( $text ) . '</p></div>';
	}

	private function after_form() {

		?><p class="description" style="padding-top:20px;clear:both">
		<?php
		esc_html_e(
			'Languages are sorted descending by priority and ascending by their English name.',
			'multilingualpress'
		);
		?>
		</p>
		<p class="description">
		<?php
		esc_html_e(
			'If you change the priority of a language to a higher value, it will show up on an earlier page.',
			'multilingualpress'
		);
		?>
		</p>

		<?php
		if ( isset ( $_GET[ 'msg' ] ) && 'resettable' === $_GET[ 'msg' ] )
			return;

		$this->get_reset_table_link();
	}

	private function before_table() {

		print '<div class="tablenav top">';
		$this->get_pagination_object()->print_pagination();
		print '</div>';
	}

	private function after_table() {

		print '<div class="tablenav bottom">';

		$this->get_pagination_object()->print_pagination();

		print '</div>';
	}

	/**
	 * @return Mlp_Table_Pagination_View
	 */
	private function get_pagination_object() {

		if ( ! is_a( $this->pagination_view, 'Mlp_Custom_Table_Pagination_View' ) )
			$this->pagination_view = new Mlp_Table_Pagination_View( $this->pagination_data );

		return $this->pagination_view;
	}

	private function show_table() {

		$view = new Mlp_Admin_Table_View (
			$this->db,
			new Mlp_Html,
			$this->pagination_data,
			$this->get_columns(),
			'mlp-language-manager-table',
			'languages'
		);
		$view->show_table();
	}

	/**
	 * @return array
	 */
	private function get_columns() {
		return array (
			'native_name' => array (
				'header'     => esc_html__( 'Native name', 'multilingualpress' ),
				'type'       => 'input_text',
				'attributes' => array (
					'size' => 20
				)
			),
			'english_name' => array (
				'header'     => esc_html__( 'English name', 'multilingualpress' ),
				'type'       => 'input_text',
				'attributes' => array (
					'size' => 20
				)
			),
			'is_rtl' => array (
				'header'     => esc_html__( 'RTL', 'multilingualpress' ),
				'type'       => 'input_checkbox',
				'attributes' => array (
					'size' => 20
				)
			),
			'http_name' => array (
				'header'     => esc_html__( 'HTTP', 'multilingualpress' ),
				'type'       => 'input_text',
				'attributes' => array (
					'size' => 5
				)
			),
			'iso_639_1' => array (
				'header'     => esc_html__( 'ISO&#160;639-1', 'multilingualpress' ),
				'type'       => 'input_text',
				'attributes' => array (
					'size' => 5
				)
			),
			'iso_639_2' => array (
				'header'     => esc_html__( 'ISO&#160;639-2', 'multilingualpress' ),
				'type'       => 'input_text',
				'attributes' => array (
					'size' => 5
				)
			),
			'wp_locale' => array (
				'header'     => esc_html__( 'wp_locale', 'multilingualpress' ),
				'type'       => 'input_text',
				'attributes' => array (
					'size' => 5
				)
			),
			'priority' => array (
				'header'     => esc_html__( 'Priority', 'multilingualpress' ),
				'type'       => 'input_number',
				'attributes' => array (
					'min'  => 1,
					'max'  => 10,
					'size' => 3
				)
			),
		);
	}
}