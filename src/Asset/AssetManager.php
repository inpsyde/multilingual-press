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
	 * Returns the script with the given handle.
	 *
	 * @since 3.0.0
	 *
	 * @param string $handle Script handle.
	 *
	 * @return Script Script object.
	 */
	public function get_script( $handle ) {

		$handle = (string) $handle;

		if ( empty( $this->scripts[ $handle ] ) ) {
			return null;
		}

		return $this->scripts[ $handle ];
	}

	/**
	 * Returns the style with the given handle.
	 *
	 * @since 3.0.0
	 *
	 * @param string $handle Script handle.
	 *
	 * @return Style Style object.
	 */
	public function get_style( $handle ) {

		$handle = (string) $handle;

		if ( empty( $this->styles[ $handle ] ) ) {
			return null;
		}

		return $this->styles[ $handle ];
	}

	/**
	 * Enqueues the script with the given handle.
	 *
	 * @since 3.0.0
	 *
	 * @param Script|string $script    Script object or handle.
	 * @param bool          $in_footer Optional. Enqueue in the footer? Defaults to true.
	 *
	 * @return bool Whether or not the script was enqueued successfully.
	 */
	public function enqueue_script( $script, $in_footer = true ) {

		$handle = (string) $script;

		if ( empty( $this->scripts[ $handle ] ) ) {
			return false;
		}

		$script = $this->scripts[ $handle ];

		if ( wp_script_is( $handle ) ) {
			$this->handle_script_data( $script );

			return true;
		}

		$this->enqueue( function () use ( $handle, $script, $in_footer ) {

			wp_enqueue_script(
				$handle,
				$script->url(),
				$script->dependencies(),
				$script->version(),
				(bool) $in_footer
			);

			$this->handle_script_data( $script );
		} );

		return true;
	}

	/**
	 * Enqueues the script with the given handle.
	 *
	 * @since 3.0.0
	 *
	 * @param Script|string $script      Script object or handle.
	 * @param string        $object_name The name of the JavaScript variable holding the data.
	 * @param array         $data        The data to be made available for the script.
	 * @param bool          $in_footer   Optional. Enqueue in the footer? Defaults to true.
	 *
	 * @return bool Whether or not the script was enqueued successfully.
	 */
	public function enqueue_script_with_data(
		$script,
		$object_name,
		array $data,
		$in_footer = true
	) {

		$handle = (string) $script;

		if ( empty( $this->scripts[ $handle ] ) ) {
			return false;
		}

		if ( ! $this->add_script_data( $this->scripts[ $handle ], $object_name, $data ) ) {
			return false;
		}

		return $this->enqueue_script( $handle, $in_footer );
	}

	/**
	 * Enqueues the style with the given handle.
	 *
	 * @since 3.0.0
	 *
	 * @param Style|string $style Style object or handle.
	 *
	 * @return bool Whether or not the style was enqueued successfully.
	 */
	public function enqueue_style( $style ) {

		$handle = (string) $style;

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
	 * Adds the given data to the given script, and handles it in case the script has been enqueued already.
	 *
	 * @since 3.0.0
	 *
	 * @param Script|string $script      Script object or handle.
	 * @param string        $object_name The name of the JavaScript variable holding the data.
	 * @param array         $data        The data to be made available for the script.
	 *
	 * @return Script|null Script object if it exists, null if not.
	 */
	public function add_script_data( $script, $object_name, array $data ) {

		if ( ! $script instanceof Script ) {
			$script = $this->get_script( (string) $script );
			if ( ! $script ) {
				return null;
			}
		}

		$script->add_data( $object_name, $data );

		if ( wp_script_is( $script->handle() ) ) {
			$this->handle_script_data( $script );
		}

		return $script;
	}

	/**
	 * Handles potential data that has been added to the script after it was enqueued, and then clears the data.
	 *
	 * @param Script $script Script object.
	 *
	 * @return void
	 */
	private function handle_script_data( Script $script ) {

		$data = $script->data();

		$handle = $script->handle();

		array_walk( $data, function ( array $data, $object_name ) use ( $handle ) {

			wp_localize_script( $handle, $object_name, $data );
		} );

		$script->clear_data();
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
