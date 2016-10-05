<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Asset;

/**
 * Managing instance for all asset-specific tasks.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
class AssetManager {

	/**
	 * @var Script[]
	 */
	private $scripts = [];

	/**
	 * @var Style[]
	 */
	private $styles = [];

	/**
	 * Register the given script.
	 *
	 * @since 3.0.0
	 *
	 * @param Script $script Script object.
	 *
	 * @return static Asset manager instance.
	 */
	public function register_script( Script $script ) {

		$this->scripts[ $script->handle() ] = $script;

		return $this;
	}

	/**
	 * Register the given style.
	 *
	 * @since 3.0.0
	 *
	 * @param Style $style Style object.
	 *
	 * @return static Asset manager instance.
	 */
	public function register_style( Style $style ) {

		$this->styles[ $style->handle() ] = $style;

		return $this;
	}

	/**
	 * Enqueues the script with the given handle.
	 *
	 * @since 3.0.0
	 *
	 * @param string $handle    Script handle.
	 * @param bool   $in_footer Optional. Enqueue in the footer? Defaults to true.
	 *
	 * @return bool Whether or not the script was enqueued successfully.
	 */
	public function enqueue_script( $handle, $in_footer = true ) {

		$handle = (string) $handle;

		if ( empty( $this->scripts[ $handle ] ) ) {
			return false;
		}

		if ( wp_script_is( $handle ) ) {
			return true;
		}

		$this->enqueue( function () use ( $handle, $in_footer ) {

			$script = $this->scripts[ $handle ];

			wp_enqueue_script(
				$handle,
				$script->url(),
				$script->dependencies(),
				$script->version(),
				(bool) $in_footer
			);

			$data = $script->data();

			array_walk( $data, function ( array $data, $object_name ) use ( $handle ) {

				wp_localize_script( $handle, $object_name, $data );
			} );
		} );

		return true;
	}

	/**
	 * Enqueues the style with the given handle.
	 *
	 * @since 3.0.0
	 *
	 * @param string $handle Asset handle.
	 *
	 * @return bool Whether or not the style was enqueued successfully.
	 */
	public function enqueue_style( $handle ) {

		$handle = (string) $handle;

		if ( empty( $this->styles[ $handle ] ) ) {
			return false;
		}

		if ( wp_style_is( $handle ) ) {
			return true;
		}

		$this->enqueue( function () use ( $handle ) {

			$style = $this->styles[ $handle ];

			wp_enqueue_style(
				$handle,
				$style->url(),
				$style->dependencies(),
				$style->version(),
				$style->media()
			);
		} );

		return true;
	}

	/**
	 * Either executes the given callback or hooks it to the appropriate enqueue action, depending on the context.
	 *
	 * @param callable $callback Enqueue callback.
	 *
	 * @return void
	 */
	private function enqueue( callable $callback ) {

		$enqueue_action = $this->get_enqueue_action();

		if ( did_action( $enqueue_action ) ) {
			add_action( $enqueue_action, $callback );
		} else {
			call_user_func( $callback );
		}
	}

	/**
	 * Returns the appropriate action for enqueueing assets.
	 *
	 * @return string Action for enqueueing assets.
	 */
	private function get_enqueue_action() {

		if ( 0 === strpos( $_SERVER['REQUEST_URI'], '/wp-login.php' ) ) {
			return empty( $GLOBALS['interim_login'] )
				? 'login_enqueue_scripts'
				: '';
		}

		if ( is_admin() ) {
			return 'admin_enqueue_scripts';
		}

		if ( is_customize_preview() ) {
			return 'customize_controls_enqueue_scripts';
		}

		return 'wp_enqueue_scripts';
	}
}
