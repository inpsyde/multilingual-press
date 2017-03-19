<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Generic meta box implementation.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
class MetaBox {

	/**
	 * @var array
	 */
	private $callback_args;

	/**
	 * @var MetaBoxModel
	 */
	private $model;

	/**
	 * @var MetaBoxView
	 */
	private $view;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param MetaBoxModel $model         Meta box model object.
	 * @param MetaBoxView  $view          Meta box view object.
	 * @param array        $callback_args Optional. Render callback arguments. Defaults to empty array.
	 */
	public function __construct( MetaBoxModel $model, MetaBoxView $view, array $callback_args = [] ) {

		$this->model = $model;

		$this->view = $view;

		$this->callback_args = $callback_args;
	}

	/**
	 * Registers the meta box.
	 *
	 * @since 3.0.0
	 *
	 * @param string|array|\WP_Screen $screen        Optional. Screen identifier. Defaults to empty string.
	 * @param string                  $context       Optional. Meta box context. Defaults to 'advanced'.
	 * @param string                  $priority      Optional. Meta box priority. Defaults to 'default'.
	 * @param array                   $callback_args Optional. Render callback arguments. Defaults to empty array.
	 *
	 * @return void
	 */
	public function register(
		$screen = '',
		string $context = 'advanced',
		string $priority = 'default',
		array $callback_args = []
	) {

		add_meta_box(
			$this->model->id(),
			esc_html( $this->model->title() ),
			[ $this->view, 'render' ],
			$screen,
			$context,
			$priority,
			$callback_args ?: $this->callback_args
		);
	}
}
