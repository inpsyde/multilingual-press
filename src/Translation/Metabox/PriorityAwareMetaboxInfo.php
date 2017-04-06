<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Metabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
interface PriorityAwareMetaboxInfo extends MetaboxInfo {

	/**
	 * @return string
	 */
	public function priority(): string;

	/**
	 * @param string $priority
	 *
	 * @return PriorityAwareMetaboxInfo
	 */
	public function with_priority( string $priority ): PriorityAwareMetaboxInfo;


}