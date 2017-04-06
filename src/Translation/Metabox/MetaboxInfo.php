<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Metabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
interface MetaboxInfo {

	const SCREEN_ID = 'screen-id';
	const SCREEN_BASE = 'screen-base';
	const SCREEN_PARENT = 'screen-parent';

	/**
	 * @return string
	 */
	public function id(): string;

	/**
	 * @param string $id
	 *
	 * @return MetaboxInfo
	 */
	public function with_id( string $id ): MetaboxInfo;

	/**
	 * @return string
	 */
	public function title(): string;

	/**
	 * @param string $title
	 *
	 * @return MetaboxInfo
	 */
	public function with_title( string $title ): MetaboxInfo;

	/**
	 * @return string
	 */
	public function context(): string;

	/**
	 * @param string $context
	 *
	 * @return MetaboxInfo
	 */
	public function with_context( string $context ): MetaboxInfo;


	/**
	 * @param \WP_Screen $screen
	 *
	 * @return bool
	 */
	public function is_allowed_for_screen( \WP_Screen $screen ): bool;

	/**
	 * @param array $screen_id
	 *
	 * @return MetaboxInfo
	 */
	public function allow_screen( ...$screen_id ): MetaboxInfo;

	/**
	 * @param array $screen_base
	 *
	 * @return MetaboxInfo
	 */
	public function allow_screen_base( ...$screen_base ): MetaboxInfo;

	/**
	 * @param array $screen_parent
	 *
	 * @return MetaboxInfo
	 */
	public function allow_screen_parent( ...$screen_parent ): MetaboxInfo;


}