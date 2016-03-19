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

	/**
	 * Plugin data
	 *
	 * @var Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * @var Mlp_Network_Site_Settings_Tab_Data
	 */
	private $tab_page_data;

	/**
	 * @var Mlp_Network_Site_Settings_Properties
	 */
	private $page_properties;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param Inpsyde_Property_List_Interface $plugin_data Plugin data.
	 *
	 * @wp-hook plugins_loaded
	 */
	public function __construct( Inpsyde_Property_List_Interface $plugin_data ) {

		$this->plugin_data = $plugin_data;
		$this->tab_page_data = new Mlp_Network_Site_Settings_Tab_Data;
		$this->page_properties = new Mlp_Network_Site_Settings_Properties( $plugin_data );

		new Mlp_Network_Site_Settings( $this->page_properties, $this );

		add_action(
			'admin_post_' . $this->tab_page_data->get_action_name(),
			array( $this, 'update_settings' )
		);

		add_action(
			'admin_print_styles-' . $this->page_properties->get_param_value(),
			array( $this, 'enqueue_stylesheet' )
		);
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|void Either a value, or void for actions.
	 */
	public function update( $name ) {

		if ( 'create_tab_content' === $name ) {
			$this->create_tab_content();
		}
	}

	/**
	 * Load stylesheet.
	 *
	 * @return void
	 */
	public function enqueue_stylesheet() {
		wp_enqueue_style( 'mlp-admin-css' );
	}

	/**
	 * Combine all update actions.
	 *
	 * @return void
	 */
	public function update_settings() {

		if ( ! check_admin_referer(
			$this->tab_page_data->get_nonce_action(),
			$this->tab_page_data->get_nonce_name()
			) )
			wp_die( 'Invalid', 'Invalid', array ( 'response' => 403 ) );

		$blog_id = $this->get_blog_id();

		$this->update_language( $blog_id );
		$this->update_flag( $blog_id );
		$this->update_related_blogs( $blog_id );

		/**
		 * Runs after having saved the site settings.
		 *
		 * @param array $_POST The $_POST superglobal.
		 */
		do_action( 'mlp_blogs_save_fields', $_POST );

		$url = add_query_arg( 'msg', 'updated', $_POST[ '_wp_http_referer' ] );
		wp_safe_redirect( $url );
		mlp_exit();
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
	 * @return int
	 */
	private function update_related_blogs( $blog_id ) {

		/** @var Mlp_Site_Relations_Interface $relations */
		$relations   = $this->plugin_data->get( 'site_relations' );
		$changed     = 0;
		$new_related = $this->get_new_related_blogs();
		$old_related = $relations->get_related_sites( $blog_id, FALSE );

		// All relations removed.
		if ( empty ( $new_related ) && ! empty ( $old_related ) )
			return $relations->delete_relation( $blog_id );

		$add_ids = $this->get_new_relations( $new_related, $old_related );

		if ( ! empty ( $add_ids ) )
			$changed += $relations->set_relation( $blog_id, $add_ids );

		if ( ! empty ( $old_related ) )
			$changed += $this->delete_unset_relations( $blog_id, $old_related, $new_related, $relations, $changed );

		return $changed;
	}

	/**
	 * Inner markup for the tab.
	 *
	 * @return void
	 */
	private function create_tab_content() {

		$this->show_update_message();

		$view = new Mlp_Network_Site_Settings_Tab_Content(
			$this->plugin_data->get( 'language_api' ),
			$this->tab_page_data,
			$this->get_blog_id(),
			$this->plugin_data->get( 'site_relations' )
		);
		$view->render_content();
	}

	/**
	 * @return int
	 */
	private function get_blog_id() {

		if ( empty ( $_REQUEST[ 'id' ] ) )
			return get_current_blog_id();

		return (int) $_REQUEST[ 'id' ];
	}

	/**
	 * Admin notices.
	 *
	 * @return void
	 */
	private function show_update_message() {

		if ( empty ( $_GET[ 'msg' ] ) or 'updated' !== $_GET[ 'msg' ] )
			return;

		$msg    = esc_html__( 'Settings saved.', 'multilingual-press' );
		$notice = new Mlp_Admin_Notice( $msg, array( 'class' => 'updated' ) );
		$notice->show();
	}

	/**
	 * @return array
	 */
	private function get_new_related_blogs() {

		if ( ! isset ( $_POST[ 'related_blogs' ] ) )
			return array();

		$new_related = (array) $_POST[ 'related_blogs' ];
		return array_map( 'intval', $new_related );
	}

	/**
	 * @param $new_related
	 * @param $old_related
	 * @return array
	 */
	private function get_new_relations( $new_related, $old_related ) {

		$add_ids = array ();

		// Set new relations.
		foreach ( $new_related as $new_blog_id ) {

			if ( 0 === $new_blog_id )
				continue;

			if ( ! in_array( $new_blog_id, $old_related ) )
				$add_ids[ ] = $new_blog_id;
		}

		return $add_ids;
	}

	/**
	 * @param $blog_id
	 * @param $old_related
	 * @param $new_related
	 * @param $relations
	 * @param $changed
	 * @return int
	 */
	private function delete_unset_relations( $blog_id, $old_related, $new_related, Mlp_Site_Relations_Interface $relations, $changed ) {

		// Delete removed relations.
		foreach ( $old_related as $old_blog_id ) {
			if ( ! in_array( $old_blog_id, $new_related ) ) {
				$relations->delete_relation( $blog_id, $old_blog_id );
				$changed += 1;
			}
		}

		return $changed;
	}

}
