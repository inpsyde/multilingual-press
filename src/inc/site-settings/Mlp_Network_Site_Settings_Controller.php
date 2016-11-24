<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\AdminNotice;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Type\Setting;

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
	 * @var Setting
	 */
	private $setting;

	/**
	 * @var Mlp_Network_Site_Settings_Properties
	 */
	private $page_properties;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @wp-hook plugins_loaded
	 *
	 * @param Inpsyde_Property_List_Interface $plugin_data Plugin data.
	 * @param Setting                         $setting     Setting object.
	 * @param Nonce                           $nonce       Nonce object.
	 */
	public function __construct( Inpsyde_Property_List_Interface $plugin_data, Setting $setting, Nonce $nonce ) {

		$this->plugin_data = $plugin_data;

		$this->setting = $setting;

		$this->nonce = $nonce;

		add_action( 'admin_post_' . $setting->action(), [ $this, 'update_settings' ] );

		$this->page_properties = new Mlp_Network_Site_Settings_Properties();

		add_action(
			'admin_print_styles-' . $this->page_properties->get_param_value(),
			[ $this, 'enqueue_stylesheet' ]
		);

		new Mlp_Network_Site_Settings( $this->page_properties, $this );
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

		$this->plugin_data->get( 'assets' )->enqueue_style( 'multilingualpress-admin' );
	}

	/**
	 * Combine all update actions.
	 *
	 * @return void
	 */
	public function update_settings() {

		if ( ! \Inpsyde\MultilingualPress\check_admin_referer( $this->nonce ) ) {
			wp_die( 'Invalid', 'Invalid', 403 );
		}

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
		\Inpsyde\MultilingualPress\call_exit();
	}

	/**
	 * @param int $blog_id
	 * @return bool
	 */
	private function update_language( $blog_id ) {

		$languages = (array) get_site_option( 'inpsyde_multilingual', [] );

		if ( empty ( $languages[ $blog_id ] ) )
			$languages[ $blog_id ] = [];

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

		/** @var SiteRelations $relations */
		$relations   = $this->plugin_data->get( 'site_relations' );
		$changed     = 0;
		$new_related = $this->get_new_related_blogs();
		$old_related = $relations->get_related_site_ids( $blog_id );

		// All relations removed.
		if ( empty ( $new_related ) && ! empty ( $old_related ) )
			return $relations->delete_relation( $blog_id );

		$add_ids = $this->get_new_relations( $new_related, $old_related );

		if ( ! empty ( $add_ids ) )
			$changed += $relations->insert_relations( $blog_id, $add_ids );

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
			$this->plugin_data->get( 'languages' ),
			$this->setting,
			$this->get_blog_id(),
			$this->plugin_data->get( 'site_relations' ),
			$this->nonce
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

		( new AdminNotice( '<p>' . __( 'Settings saved.', 'multilingual-press' ) . '</p>' ) )->render();
	}

	/**
	 * @return array
	 */
	private function get_new_related_blogs() {

		if ( ! isset ( $_POST[ 'related_blogs' ] ) )
			return [];

		$new_related = (array) $_POST[ 'related_blogs' ];
		return array_map( 'intval', $new_related );
	}

	/**
	 * @param $new_related
	 * @param $old_related
	 * @return array
	 */
	private function get_new_relations( $new_related, $old_related ) {

		$add_ids = [];

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
	private function delete_unset_relations( $blog_id, $old_related, $new_related, SiteRelations $relations, $changed ) {

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
