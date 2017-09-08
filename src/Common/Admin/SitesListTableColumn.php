<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Model for a custom column in the Sites list table in the Network Admin.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
class SitesListTableColumn {

	/**
	 * @var callable|null
	 */
	private $add_callback;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var callable
	 */
	private $render_callback;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string        $id              Column ID.
	 * @param string        $name            Column name.
	 * @param callable      $render_callback Callback for rendering the column content.
	 * @param callable|null $add_callback    Optional. Callback to handle adding the column. Defaults to null.
	 */
	public function __construct(
		string $id,
		string $name,
		callable $render_callback,
		$add_callback = null
	) {

		$this->id = $id;

		$this->name = $name;

		$this->render_callback = $render_callback;

		$this->add_callback = $add_callback;
	}

	/**
	 * Registers the column methods by using the appropriate WordPress hooks.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register() {

		add_filter( 'wpmu_blogs_columns', [ $this, 'add' ] );

		add_action( 'manage_sites_custom_column', [ $this, 'render_content' ], 10, 2 );
	}

	/**
	 * Adds the column.
	 *
	 * @since   3.0.0
	 * @wp-hook wpmu_blogs_columns
	 *
	 * @param array $columns The current columns.
	 *
	 * @return array All columns.
	 */
	public function add( array $columns ): array {

		if ( $this->add_callback && is_callable( $this->add_callback ) ) {
			$callback = $this->add_callback;

			return (array) $callback( $columns, $this->id, $this->name );
		}

		return array_merge( $columns, [
			$this->id => $this->name,
		] );
	}

	/**
	 * Renders the column content.
	 *
	 * @since   3.0.0
	 * @wp-hook manage_sites_custom_column
	 *
	 * @param string $id      Column ID.
	 * @param int    $site_id Site ID.
	 *
	 * @return bool Whether or not the content was rendered successfully.
	 */
	public function render_content( $id, $site_id ): bool {

		if ( $id === $this->id ) {
			$callback = $this->render_callback;

			echo $callback( $id, (int) $site_id );

			return true;
		}

		return false;
	}
}
