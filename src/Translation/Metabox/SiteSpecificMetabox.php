<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Metabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
interface SiteSpecificMetabox extends Metabox {

	/**
	 * @return int
	 */
	public function site_id(): int;

}