<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

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
	 * @return AssetManager Asset manager instance.
	 */
	public function register_style( Style $style ): AssetManager {

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
	 * @return Script|null Script object, or null.
	 */
	public function get_script( string $handle ) {

		return $this->scripts[ $handle ] ?? null;
	}

	/**
	 * Returns the style with the given handle.
	 *
	 * @since 3.0.0
	 *
	 * @param string $handle Script handle.
	 *
	 * @return Style|null Style object, or null.
	 */
	public function get_style( string $handle ) {

		return $this->styles[ $handle ] ?? null;
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
	public function enqueue_script( string $handle, bool $in_footer = true ): bool {

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
				$in_footer
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
	 * @param string $handle      Script handle.
	 * @param string $object_name The name of the JavaScript variable holding the data.
	 * @param array  $data        The data to be made available for the script.
	 * @param bool   $in_footer   Optional. Enqueue in the footer? Defaults to true.
	 *
	 * @return bool Whether or not the script was enqueued successfully.
	 */
	public function enqueue_script_with_data(
		string $handle,
		string $object_name,
		array $data,
		bool $in_footer = true
	): bool {

		if ( empty( $this->scripts[ $handle ] ) ) {
			return false;
		}

		if ( ! $this->add_script_data( $handle, $object_name, $data ) ) {
			return false;
		}

		return $this->enqueue_script( $handle, $in_footer );
	}

	/**
	 * Enqueues the style with the given handle.
	 *
	 * @since 3.0.0
	 *
	 * @param string $handle Style handle.
	 *
	 * @return bool Whether or not the style was enqueued successfully.
	 */
	public function enqueue_style( string $handle ): bool {

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
	 * @param string $handle      Script handle.
	 * @param string $object_name The name of the JavaScript variable holding the data.
	 * @param array  $data        The data to be made available for the script.
	 *
	 * @return Script|null Script object, or null.
	 */
	public function add_script_data( string $handle, string $object_name, array $data ) {

		$script = $this->get_script( $handle );
		if ( ! $script ) {
			return null;
		}

		$script->add_data( $object_name, $data );

		if ( wp_script_is( $handle ) ) {
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
			$callback();

			return;
		}

		add_action( $enqueue_action, $callback );
	}

	/**
	 * Returns the appropriate action for enqueueing assets.
	 *
	 * @return string Action for enqueueing assets.
	 */
	private function get_enqueue_action(): string {

		if ( 0 === strpos( ltrim( add_query_arg( [] ), '/' ), 'wp-login.php' ) ) {
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
