<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Metabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
interface Metabox {

	/**
	 * @return MetaboxInfo
	 */
	public function info(): MetaboxInfo;

	/**
	 * @return MetaboxView
	 */
	public function view(): MetaboxView;

	/**
	 * @return MetaboxUpdater
	 */
	public function updater(): MetaboxUpdater;

}