<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Widget\Dashboard\DashboardWidgetOptions;

/**
 * Untranslated posts widget configurator.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
class WidgetConfigurator {

	use DashboardWidgetOptions;

	/**
	 * Setting name base.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_BASE = 'mlp_untranslated_posts';

	/**
	 * Setting name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_DISPLAY_REMOTE_SITES = 'display_remote_sites';

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * @var string
	 */
	private $widget_id = '';

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ServerRequest $server_request Server request object.
	 */
	public function __construct( ServerRequest $server_request ) {

		$this->server_request = $server_request;
	}

	/**
	 * Checks if the widget is set to display remote sites.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whethter or not the widget is displaying remote sites.
	 */
	public function is_displaying_remote_sites(): bool {

		return (bool) $this->get_option( $this->widget_id, static::NAME_DISPLAY_REMOTE_SITES, false );
	}

	/**
	 * Saves the widget options included in the current request.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the options have been updated successfully.
	 */
	public function update(): bool {

		if ( ! $this->is_update_request() ) {
			return false;
		}

		$request_data = $this->server_request->body_value(
			static::NAME_BASE,
			INPUT_POST,
			FILTER_UNSAFE_RAW,
			FILTER_FORCE_ARRAY
		);

		return $this->update_option(
			$this->widget_id,
			static::NAME_DISPLAY_REMOTE_SITES,
			! empty( $request_data[ static::NAME_DISPLAY_REMOTE_SITES ] )
		);
	}

	/**
	 * Returns a new instance with the given widget ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $widget_id Widget ID.
	 *
	 * @return WidgetConfigurator
	 */
	public function with_widget_id( string $widget_id ): WidgetConfigurator {

		$clone = clone $this;

		$clone->widget_id = $widget_id;

		return $clone;
	}

	/**
	 * Checks if the current request is a valid update request.
	 *
	 * @return bool Whether or not the current request is a valid update request.
	 */
	private function is_update_request(): bool {

		return
			$this->widget_id === $this->server_request->body_value( 'edit', INPUT_GET )
			&& $this->widget_id === $this->server_request->body_value( 'widget_id', INPUT_POST );
	}
}
