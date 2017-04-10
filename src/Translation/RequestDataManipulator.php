<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Translation;

/**
 * Interface for all request data manipulator implementations.
 *
 * @package Inpsyde\MultilingualPress\Translation
 * @since   3.0.0
 */
interface RequestDataManipulator {

	/**
	 * Request method.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const METHOD_GET = 'GET';

	/**
	 * Request method.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const METHOD_POST = 'POST';

	/**
	 * Removes all data from the request global.
	 *
	 * @since 3.0.0
	 *
	 * @return int Number of cleared elements.
	 */
	public function clear_data(): int;

	/**
	 * Restores all data from the storage.
	 *
	 * @since 3.0.0
	 *
	 * @return int Number of restored elements.
	 */
	public function restore_data(): int;
}
