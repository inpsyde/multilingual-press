<?php # -*- coding: utf-8 -*-

class Mlp_Dismissible_Notice {

	const ACTION_FOR_GOOD = 'dismiss_admin_notice_for_good';

	const ACTION_FOR_NOW = 'dismiss_admin_notice_for_now';

	const ACTION_FOR_USER = 'dismiss_admin_notice_for_user';

	const ACTION_SKIP = 'skip_action';

	const OPTION_PREFIX = 'mlp_notice_';

	/**
	 * @var string[]
	 */
	private static $all_actions = array(
		self::ACTION_FOR_GOOD,
		self::ACTION_FOR_NOW,
		self::ACTION_FOR_USER,
	);

	/**
	 * @var string[][]
	 */
	private static $setup = array(
		'blog'     => array(),
		'sitewide' => array(),
	);

	/**
	 * @var bool
	 */
	private $sitewide;

	/**
	 * @param bool   $sitewide
	 * @param string $notice_id
	 * @param string $capability
	 */
	public static function register( $sitewide, $notice_id, $capability = 'read' ) {

		if ( ! is_string( $notice_id ) ) {
			return;
		}

		$sitewide = $sitewide && is_multisite();

		$key = $sitewide ? 'sitewide' : 'blog';

		if ( array_key_exists( $notice_id, self::$setup[ $key ] ) ) {
			return;
		}

		if ( array() === self::$setup[ $key ] ) {
			$callback = array( new self( $sitewide ), 'dismiss' );

			add_action( 'admin_post_' . self::ACTION_FOR_GOOD, $callback );
			add_action( 'admin_post_' . self::ACTION_FOR_NOW, $callback );
			add_action( 'admin_post_' . self::ACTION_FOR_USER, $callback );
		}

		self::$setup[ $key ][ $notice_id ] = $capability;
	}

	/**
	 * Returns the URL that can be used to dismiss a given notice for good or temporarily according to given action.
	 *
	 * @param string $notice_id
	 * @param string $action
	 *
	 * @return string
	 */
	public static function dismiss_action_url( $notice_id, $action ) {

		return add_query_arg( array(
			'action' => $action,
			'notice' => $notice_id,
			'blog'   => get_current_blog_id(),
			$action  => wp_create_nonce( $action ),
		), admin_url( 'admin-post.php' ) );
	}

	/**
	 * @param bool $sitewide
	 */
	public function __construct( $sitewide = false ) {

		$this->sitewide = $sitewide;
	}

	/**
	 * Returns true when given notice is dismissed for good or temporarily for current user.
	 *
	 * @param $notice_id
	 *
	 * @return bool
	 */
	public function is_dismissed( $notice_id ) {

		$option_name = $this->option_name( $notice_id );

		if ( ( $this->sitewide && get_site_option( $option_name ) ) || get_option( $option_name ) ) {
			// Dismissed for good.
			return true;
		}

		if ( get_user_option( $option_name ) ) {
			// Dismissed for user.
			return true;
		}

		$transient_name = $this->transient_name( $notice_id );

		// Dismissed for now?
		return ( $this->sitewide && get_site_transient( $transient_name ) ) || get_transient( $transient_name );
	}

	/**
	 * Action callback to dismiss a notice for good.
	 */
	public function dismiss() {

		list( $action, $notice_id, $is_ajax ) = $this->assert_allowed();

		switch ( $action ) {
			case self::ACTION_FOR_GOOD:
				$this->dismiss_for_good( $notice_id );
				break;

			case self::ACTION_FOR_NOW:
				$this->dismiss_for_now( $notice_id );
				break;

			case self::ACTION_FOR_USER:
				$this->dismiss_for_user( $notice_id );
				break;

			case self::ACTION_SKIP:
				return;
		}

		$this->end_request( $is_ajax );
	}

	/**
	 * Action callback to dismiss a notice for good.
	 *
	 * @param string $notice_id
	 */
	private function dismiss_for_good( $notice_id ) {

		$option_name = $this->option_name( $notice_id );

		$this->sitewide
			? update_site_option( $option_name, 1 )
			: update_option( $option_name, 1, false );
	}

	/**
	 * Action callback to dismiss a notice temporarily for current user.
	 *
	 * @param string $notice_id
	 */
	private function dismiss_for_now( $notice_id ) {

		$transient_name = $this->transient_name( $notice_id );

		$expiration = 12 * HOUR_IN_SECONDS;

		$this->sitewide
			? set_site_transient( $transient_name, 1, $expiration )
			: set_transient( $transient_name, 1, $expiration );
	}

	/**
	 * Action callback to dismiss a notice for the current user.
	 *
	 * @param string $notice_id
	 */
	private function dismiss_for_user( $notice_id ) {

		update_user_option(
			get_current_user_id(),
			$this->option_name( $notice_id ),
			1,
			$this->sitewide
		);
	}

	/**
	 * Ends a request redirecting to referer page.
	 *
	 * @param bool $no_redirect
	 */
	private function end_request( $no_redirect = false ) {

		if ( $no_redirect ) {
			exit();
		}

		$referer = wp_get_raw_referer();
		if ( ! $referer ) {
			$referer = ( $this->sitewide && is_super_admin() ) ? network_admin_url() : admin_url();
		}

		wp_safe_redirect( $referer );

		exit();
	}

	/**
	 * @return array
	 */
	private function assert_allowed() {

		if ( ! is_admin() ) {
			$this->end_request();
		}

		$definition = array(
			'action' => FILTER_SANITIZE_STRING,
			'notice' => FILTER_SANITIZE_STRING,
			'blog'   => FILTER_SANITIZE_NUMBER_INT,
			'isAjax' => FILTER_VALIDATE_BOOLEAN,
		);

		$data = array_merge(
			array_filter( (array) filter_input_array( INPUT_GET, $definition ) ),
			array_filter( (array) filter_input_array( INPUT_POST, $definition ) )
		);

		$action = isset( $data['action'] ) ? $data['action'] : '';

		$notice = isset( $data['notice'] ) ? $data['notice'] : '';

		$is_ajax = ! empty( $data['isAjax'] );

		if (
			! $action
			|| ! $notice
			|| ! is_string( $notice )
			|| ! in_array( $action, self::$all_actions, true )
		) {
			$this->end_request( $is_ajax );
		}

		$key = $this->sitewide ? 'sitewide' : 'blog';

		$capability = empty( self::$setup[ $key ][ $notice ] ) ? '' : self::$setup[ $key ][ $notice ];

		$swap_key = $this->sitewide ? 'blog' : 'sitewide';

		if ( ! $capability && ! empty( self::$setup[ $swap_key ][ $notice ] ) ) {
			return array( self::ACTION_SKIP, '', $is_ajax );
		}

		if ( ! $capability || ! current_user_can( $capability ) ) {
			$this->end_request( $is_ajax );
		}

		$nonce = filter_input( INPUT_POST, $action, FILTER_SANITIZE_STRING );
		if ( ! $nonce ) {
			$nonce = filter_input( INPUT_GET, $action, FILTER_SANITIZE_STRING );
		}

		if ( ! $nonce || ! wp_verify_nonce( $nonce, $action ) ) {
			$this->end_request( $is_ajax );
		}

		if (
			! $this->sitewide
			&& ( empty( $data['blog'] ) || (int) get_current_blog_id() !== (int) $data['blog'] )
		) {
			$this->end_request( $is_ajax );
		}

		return array( $action, $notice, $is_ajax );
	}

	/**
	 * @param string $notice_id
	 *
	 * @return string
	 */
	private function option_name( $notice_id ) {

		return self::OPTION_PREFIX . $notice_id;
	}

	/**
	 * @param string $notice_id
	 *
	 * @return string
	 */
	private function transient_name( $notice_id ) {

		return self::OPTION_PREFIX . $notice_id . get_current_user_id();
	}
}
