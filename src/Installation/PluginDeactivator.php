<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Common\Admin\AdminNotice;

/**
 * Deactivates specific plugin.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
class PluginDeactivator {

	/**
	 * @var string[]
	 */
	private $errors;

	/**
	 * @var string
	 */
	private $plugin_base_name;

	/**
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $plugin_base_name The base name of the plugin.
	 * @param string $plugin_name      The name of the plugin.
	 * @param string[] $errors         Optional. Error messages. Defaults to empty array.
	 */
	public function __construct( $plugin_base_name, $plugin_name, array $errors = [] ) {

		$this->plugin_base_name = (string) $plugin_base_name;

		$this->plugin_name = (string) $plugin_name;

		$this->errors = $errors;
	}

	/**
	 * Deactivates the plugin, and renders an according admin notice.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function deactivate_plugin() {

		deactivate_plugins( $this->plugin_base_name );

		// Suppress the "Plugin activated" notice.
		unset( $_GET['activate'] );

		$this->render_admin_notice();
	}

	/**
	 * Renders an admin notice informing about the plugin deactivation, including potential error messages.
	 *
	 * @return void
	 */
	private function render_admin_notice() {

		$content = sprintf(
			'<p><strong>%s</strong></p>%s',
			sprintf(
				esc_html__( 'The plugin %s has been deactivated.', 'multilingual-press' ),
				$this->plugin_name
			),
			$this->get_errors_as_string()
		);

		( new AdminNotice( $content, [
			'type' => $this->errors ? 'error' : 'info',
		] ) )->render();
	}

	/**
	 * Returns the according string for all error messages to be displayed in the admin notice.
	 *
	 * @return string Error messages.
	 */
	private function get_errors_as_string() {

		if ( ! $this->errors ) {
			return '';
		}

		$errors = array_map( function ( $error ) {

			return "<p>$error</p>";
		}, $this->errors );

		return implode( '', $errors );
	}
}
