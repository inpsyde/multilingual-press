<?php
/**
 * Controller for the relationship management above the Advanced Translator.
 *
 * @author  Inpsyde GmbH, toscho
 * @version 2014.10.10
 * @license GPL
 */
class Mlp_Relationship_Control implements Mlp_Updatable {

	/**
	 * Passed by main controller.
	 *
	 * @var Inpsyde_Property_List_Interface
	 */
	private $plugin;

	/**
	 * Unique prefix to detect our registered actions and form names.
	 *
	 * @var string
	 */
	private $prefix = 'mlp_rc';

	/**
	 * @var Mlp_Relationship_Control_Data
	 */
	private $data;

	/**
	 * Constructor
	 *
	 * @uses  Mlp_Relationship_Control_Data
	 * @param Inpsyde_Property_List_Interface $plugin
	 */
	public function __construct( Inpsyde_Property_List_Interface $plugin ) {

		$this->plugin = $plugin;

		$this->data = new Mlp_Relationship_Control_Data();

		$action = $this->get_ajax_action();
		if ( $action ) {
			$this->set_up_ajax( $action );
		} else {
			add_action( 'mlp_translation_meta_box_bottom', array( $this, 'set_up_meta_box_handlers' ), 200, 3 );
		}
	}

	/**
	 * Register AJAX callbacks.
	 *
	 * @param string $action AJAX action.
	 *
	 * @return void
	 */
	public function set_up_ajax( $action ) {

		$callback_type = "{$this->prefix}_remote_post_search" === $action ? 'search' : 'reconnect';

		add_action( "wp_ajax_{$action}", array( $this, "ajax_{$callback_type}_callback" ) );
	}

	/**
	 * Callback for AJAX search.
	 *
	 * @uses   Mlp_Relationship_Control_Ajax_Search
	 * @return void
	 */
	public function ajax_search_callback() {

		$search = new Mlp_Relationship_Control_Ajax_Search( $this->data );
		$search->send_response();
	}

	/**
	 * Callback for AJAX reconnect.
	 *
	 * @uses   Mlp_Relationship_Changer
	 * @return void
	 */
	public function ajax_reconnect_callback() {

		$action = (string) filter_input( INPUT_POST, 'action' );
		$start = strlen( $this->prefix ) + 1;
		$func = substr( $action, $start ) . '_post';

		$reconnect = new Mlp_Relationship_Changer( $this->plugin );
		$reconnect->$func();

		status_header( 200 );

		mlp_exit();
	}

	/**
	 * Create the UI above the Advanced Translator metabox.
	 *
	 * @wp-hook mlp_translation_meta_box_bottom
	 * @uses    Mlp_Relationship_Control_Meta_Box_View
	 * @param   WP_Post $post
	 * @param   int     $remote_site_id
	 * @param   WP_Post $remote_post
	 * @return void
	 */
	public function set_up_meta_box_handlers(
		WP_Post $post,
				$remote_site_id,
		WP_Post $remote_post
	) {

		global $pagenow;

		if ( 'post-new.php' === $pagenow ) {
			return; // maybe later, for now, we work on existing posts only
		}

		$this->data->set_ids( array(
			'source_post_id' => $post->ID,
			'source_site_id' => get_current_blog_id(),
			'remote_site_id' => $remote_site_id,
			'remote_post_id' => $remote_post->ID,
		) );
		$view = new Mlp_Relationship_Control_Meta_Box_View( $this->data, $this );
		$view->render();
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|void Either a value, or void for actions.
	 */
	public function update( $name ) {

		if ( 'default.remote.posts' === $name ) {
			$search = new Mlp_Relationship_Control_Ajax_Search( $this->data );
			$search->render();
		}
	}

	/**
	 * Check if this is our AJAX request and returns the action.
	 *
	 * @return string
	 */
	private function get_ajax_action() {

		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return '';
		}

		$action = (string) filter_input( INPUT_POST, 'action' );
		if ( '' === $action ) {
			return '';
		}

		return 0 === strpos( $action, $this->prefix ) ? $action : '';
	}
}
