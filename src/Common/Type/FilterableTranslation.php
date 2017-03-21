<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Translation data type implementation providing a (suppressible) filter for the remote URL.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
final class FilterableTranslation implements Translation {

	/**
	 * @var string
	 */
	private $icon_url;

	/**
	 * @var Language
	 */
	private $language;

	/**
	 * @var string
	 */
	private $remote_title;

	/**
	 * @var string
	 */
	private $remote_url;

	/**
	 * @var int
	 */
	private $source_site_id;

	/**
	 * @var bool
	 */
	private $suppress_filters;

	/**
	 * @var int
	 */
	private $target_content_id;

	/**
	 * @var int
	 */
	private $target_site_id;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param array    $args     Translation arguments.
	 * @param Language $language Language object.
	 */
	public function __construct( array $args, Language $language ) {

		// TODO: Passing all the (different) stuff via an array really should be improved! Use fluent setters instead?!

		$this->icon_url = (string) ( $args['icon_url'] ?? '' );

		$this->remote_title = (string) ( $args['remote_title'] ?? '' );

		$this->remote_url = (string) ( $args['remote_url'] ?? '' );

		$this->source_site_id = (int) ( $args['source_site_id'] ?? 0 );

		$this->suppress_filters = ! empty( $args['suppress_filters'] );

		$this->target_content_id = (int) ( $args['target_content_id'] ?? 0 );

		$this->target_site_id = (int) ( $args['target_site_id'] ?? 0 );

		$this->type = (string) ( $args['type'] ?? '' );

		$this->language = $language;
	}

	/**
	 * Returns the icon URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string Icon URL.
	 */
	public function icon_url(): string {

		return $this->icon_url;
	}

	/**
	 * Returns the language object.
	 *
	 * @since 3.0.0
	 *
	 * @return Language Language object.
	 */
	public function language(): Language {

		return $this->language;
	}

	/**
	 * Returns the remote title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote title.
	 */
	public function remote_title(): string {

		return $this->remote_title;
	}

	/**
	 * Returns the remote URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote URL.
	 */
	public function remote_url(): string {

		if ( $this->suppress_filters ) {
			return $this->remote_url;
		}

		/**
		 * Filters the URL of the remote element.
		 *
		 * @since 3.0.0
		 *
		 * @param string      $remote_url        URL of the remote element.
		 * @param int         $target_site_id    ID of the target site.
		 * @param int         $target_content_id ID of the target element.
		 * @param Translation $translation       Translation object.
		 */
		$remote_url = (string) apply_filters(
			Translation::FILTER_URL,
			$this->remote_url,
			$this->target_site_id(),
			$this->target_content_id(),
			$this
		);

		return $remote_url;
	}

	/**
	 * Returns the source site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Source site ID.
	 */
	public function source_site_id(): int {

		return $this->source_site_id;
	}

	/**
	 * Returns the target content ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target content ID.
	 */
	public function target_content_id(): int {

		return $this->target_content_id;
	}

	/**
	 * Returns the target site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target site ID.
	 */
	public function target_site_id(): int {

		return $this->target_site_id;
	}

	/**
	 * Returns the content type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Content type.
	 */
	public function type(): string {

		return $this->type;
	}
}
