<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

/**
 * Redirect target data type.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
class RedirectTarget {

	/**
	 * Array key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY_LANGUAGE = 'language';

	/**
	 * Array key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY_PRIORITY = 'priority';

	/**
	 * Array key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY_SITE_ID = 'site_id';

	/**
	 * Array key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY_URL = 'url';

	/**
	 * @var string
	 */
	private $language;

	/**
	 * @var int
	 */
	private $priority;

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Optional. Redirect data. Defaults to empty array.
	 */
	public function __construct( array $data = [] ) {

		$data = array_merge( [
			self::KEY_LANGUAGE => '',
			self::KEY_PRIORITY => 0,
			self::KEY_SITE_ID  => 0,
			self::KEY_URL      => '',
		], $data );

		$this->language = (string) $data[ self::KEY_LANGUAGE ];

		$this->priority = (int) $data[ self::KEY_PRIORITY ];

		$this->site_id = (int) $data[ self::KEY_SITE_ID ];

		$this->url = (string) $data[ self::KEY_URL ];
	}

	/**
	 * Returns the target language.
	 *
	 * @since 3.0.0
	 *
	 * @return string The target language.
	 */
	public function language() {

		return $this->language;
	}

	/**
	 * Returns the target language priority.
	 *
	 * @since 3.0.0
	 *
	 * @return int The target language priority.
	 */
	public function priority() {

		return $this->priority;
	}

	/**
	 * Returns the target site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int The target site ID.
	 */
	public function site_id() {

		return $this->site_id;
	}

	/**
	 * Returns the target URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string The target URL.
	 */
	public function url() {

		return $this->url;
	}
}
