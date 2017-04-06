<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Metabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
interface MetaboxView {

	/**
	 * @param array $data
	 *
	 * @return MetaboxView
	 */
	public function with_data( array $data ): MetaboxView;

	/**
	 * @return string
	 */
	public function render(): string;

}