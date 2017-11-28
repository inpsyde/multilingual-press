<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common;
/**
 * Interface Labels
 *
 * List of labels, probably translated.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
interface Labels
{
	/**
	 * Returns one specific label.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function label( string $name ) : string;

	/**
	 * Returns all available labels.
	 *
	 * @return array
	 */
	public function all() : array;
}