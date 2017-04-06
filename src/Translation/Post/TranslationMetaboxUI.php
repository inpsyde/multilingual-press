<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
interface TranslationMetaboxUI {

	/**
	 * @return string
	 */
	public function id(): string;

	/**
	 * @return string
	 */
	public function title(): string;

	/**
	 * @return void
	 */
	public function setup_view();

	/**
	 * @return void
	 */
	public function setup_updater();
}