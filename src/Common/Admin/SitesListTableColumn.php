<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Model for a custom column in the Sites list table in the Network Admin.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
class SitesListTableColumn {

	/**
	 * @var callable
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
	 * @param string   $id              Column ID.
	 * @param string   $name            Column name.
	 * @param callable $render_callback Callback for rendering the column content.
	 * @param callable $add_callback    Optional. Callback to handle adding the column. Defaults to null.
	 */
	public function __construct(
		$id,
		$name,
		callable $render_callback,
		callable $add_callback = null
	) {

		$this->id = (string) $id;

		$this->name = (string) $name;

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
	public function add( array $columns ) {

		if ( is_callable( $this->add_callback ) ) {
			return (array) call_user_func( [ $this, 'add_callback' ], $columns, $this->id, $this->name );
		}

		return array_merge( $columns, [ $this->id => $this->name ] );
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
	public function render_content( $id, $site_id ) {

		if ( $id === $this->id ) {
			echo call_user_func( $this->render_callback, $id, $site_id );

			return true;
		}

		return false;
	}
}
