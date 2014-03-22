<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Network_Site_Settings_Controller
 *
 * Handle settings for the whole network.
 *
 * @version 2014.01.15
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings_Controller implements Mlp_Updatable {

	private $plugin_data;
	private $tab_page_data;
	private $page_properties;
	/**
	 * Constructor.
	 *
	 * @wp-hook plugins_loaded
	 */
	public function __construct( Inpsyde_Property_List_Interface $plugin_data ) {

		$this->plugin_data     = $plugin_data;
		$this->tab_page_data   = new Mlp_Network_Site_Settings_Tab_Data;
		$this->page_properties = new Mlp_Network_Site_Settings_Properties( $plugin_data );

		new Mlp_Network_Site_Settings( $this->page_properties, $this );

		add_action(
			'admin_post_' . $this->tab_page_data->get_action_name(),
			array ( $this, 'update_settings' )
		);

		add_action(
			'admin_print_styles-' . $this->page_properties->get_param_value(),
			array ( $this, 'enqueue_stylesheet' )
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mlp_Updatable::update()
	 */
	public function update( $name ) {

		if ( 'create_tab_content' === $name )
			$this->create_tab_content();
	}

	public function enqueue_stylesheet() {
		wp_enqueue_style( 'mlp-admin-css' );
	}

	public function update_settings() {

		if ( ! check_admin_referer(
			$this->tab_page_data->get_nonce_action(),
			$this->tab_page_data->get_nonce_name()
			) )
			die( 'invalid' );

		$blog_id = $this->get_blog_id();

		$this->update_language( $blog_id );
		$this->update_flag( $blog_id );
		$this->update_related_blogs( $blog_id );

		do_action( 'mlp_blogs_save_fields', $_POST );

		$url = add_query_arg( 'msg', 'updated', $_POST[ '_wp_http_referer' ] );
		wp_safe_redirect( $url );
		exit;

	}

	/**
	 * @param int $blog_id
	 * @return bool
	 */
	private function update_language( $blog_id ) {

		$languages = (array) get_site_option( 'inpsyde_multilingual', array() );

		if ( empty ( $languages[ $blog_id ] ) )
			$languages[ $blog_id ] = array ();

		if ( ! isset ( $_POST[ 'inpsyde_multilingual_lang' ] )
			or '-1' === $_POST[ 'inpsyde_multilingual_lang' ]
			) {
			unset ( $languages[ $blog_id ][ 'lang' ] );
		}
		else {
			$languages[ $blog_id ][ 'lang' ] = $_POST[ 'inpsyde_multilingual_lang' ];

			// Set alternate title
			if ( isset( $_POST[ 'inpsyde_multilingual_text' ] ) ) {
				$languages[ $blog_id ][ 'text' ] = $_POST[ 'inpsyde_multilingual_text' ];
			}
		}

		return update_site_option( 'inpsyde_multilingual', $languages );
	}

	/**
	 * @param int $blog_id
	 * @return bool
	 */
	private function update_flag( $blog_id ) {

		$flag_url = '';

		if ( isset ( $_POST[ 'inpsyde_multilingual_flag_url' ] ) )
			$flag_url = esc_url( $_POST[ 'inpsyde_multilingual_flag_url' ] );

		return update_blog_option( $blog_id, 'inpsyde_multilingual_flag_url', $flag_url );
	}

	/**
	 * @param int $blog_id
	 * @return bool
	 */
	private function update_related_blogs( $blog_id ) {

		$key         = 'inpsyde_multilingual_blog_relationship';
		$new_related = array();
		$old_related = (array) get_blog_option( $blog_id, $key );

		if ( isset ( $_POST[ 'related_blogs' ] ) )
			$new_related = $_POST[ 'related_blogs' ];

		if ( $new_related !== $old_related )
			$this->update_remote_blog_relationships( $blog_id, $new_related, $old_related );

		return update_blog_option( $blog_id, $key, $new_related );
	}

	/**
	 * Update the options for the old and new related blogs.
	 *
	 * @param  int   $blog_id Current blog ID
	 * @param  array $new_related New relationships
	 * @param  array $old_related Old relationships
	 * @return int                Number of processed blogs.
	 */
	private function update_remote_blog_relationships( $blog_id, Array $new_related, Array $old_related ) {

		$processed = array();

		foreach ( $new_related as $new_blog_id ) {
			$remote_relations   = get_blog_option( $new_blog_id, 'inpsyde_multilingual_blog_relationship', array() );
			$remote_relations[] = $blog_id;
			$remote_relations   = array_unique( $remote_relations );
			update_blog_option( $new_blog_id, 'inpsyde_multilingual_blog_relationship', $remote_relations );

			$processed[ $new_blog_id ] = 1;
		}

		unset ( $remote_relations );

		foreach ( $old_related as $old_blog_id ) {

			if ( isset ( $processed[ $old_blog_id ] ) )
				continue;

			$remote_relations = get_blog_option( $old_blog_id, 'inpsyde_multilingual_blog_relationship', array() );
			$remote_relations = array_unique( $remote_relations );
			$key              = array_search( $blog_id, $remote_relations );

			if ( FALSE === $key )
				continue;

			unset ( $remote_relations[ $key ] );
			update_blog_option( $old_blog_id, 'inpsyde_multilingual_blog_relationship', $remote_relations );

			$processed[ $old_blog_id ] = 1;
		}

		return count( $processed );
	}

	private function create_tab_content() {

		$this->show_update_message();

		$view = new Mlp_Network_Site_Settings_Tab_Content(
			$this->plugin_data->language_api,
			$this->tab_page_data,
			$this->get_blog_id()
		);
		$view->render_content();
	}

	/**
	 * @return int
	 */
	private function get_blog_id() {

		if ( ! isset ( $_REQUEST[ 'id' ] ) )
			return get_current_blog_id();

		return (int) $_REQUEST[ 'id' ];
	}

	private function show_update_message() {

		if ( empty ( $_GET[ 'msg' ] ) or 'updated' !== $_GET[ 'msg' ] )
			return;

		$msg    = esc_html__( 'Settings saved.', 'multilingualpress' );
		$notice = new Mlp_Admin_Notice( $msg, array( 'class' => 'updated' ) );
		$notice->show();
	}
}