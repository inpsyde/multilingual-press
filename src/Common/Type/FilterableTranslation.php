<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Translation data type implementation providing a (suppressible) filter for the remote URL.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
class FilterableTranslation implements Translation {

	/**
	 * @var URL
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
	 * @var URL
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

		$this->icon_url = ( $args['icon_url'] instanceof URL )
			? $args['icon_url']
			: EscapedURL::create( '' );

		$this->remote_title = (string) $args['remote_title'];

		$this->remote_url = ( $args['remote_url'] instanceof URL )
			? $args['remote_url']
			: EscapedURL::create( '' );

		$this->source_site_id = (int) $args['source_site_id'];

		$this->suppress_filters = ! empty( $args['suppress_filters'] );

		$this->target_content_id = (int) $args['target_content_id'];

		$this->target_site_id = (int) $args['target_site_id'];

		$this->type = (string) $args['type'];

		$this->language = $language;
	}

	/**
	 * Returns the icon URL object.
	 *
	 * @since 3.0.0
	 *
	 * @return URL Icon URL object.
	 */
	public function get_icon_url() {

		return $this->icon_url;
	}

	/**
	 * Returns the language object.
	 *
	 * @since 3.0.0
	 *
	 * @return Language Language object.
	 */
	public function get_language() {

		return $this->language;
	}

	/**
	 * Returns the content type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Content type.
	 */
	public function get_type() {

		return $this->type;
	}

	/**
	 * Returns the remote title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote title.
	 */
	public function get_remote_title() {

		return $this->remote_title;
	}

	/**
	 * Returns the remote URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote URL.
	 */
	public function get_remote_url() {

		if ( $this->suppress_filters ) {
			return (string) $this->remote_url;
		}

		/**
		 * Filters the remote URL of the linked element.
		 *
		 * @since 1.0.3
		 * @since 2.2.0 Added the `$translation` argument.
		 *
		 * @param string      $remote_url        URL of the remote element.
		 * @param int         $target_site_id    ID of the target site.
		 * @param int         $target_content_id ID of the target element.
		 * @param Translation $translation       Translation object.
		 */
		$remote_url = (string) apply_filters(
			'mlp_linked_element_link',
			(string) $this->remote_url,
			$this->get_target_site_id(),
			$this->get_target_content_id(),
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
	public function get_source_site_id() {

		return $this->source_site_id;
	}

	/**
	 * Returns the target content ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target content ID.
	 */
	public function get_target_content_id() {

		return $this->target_content_id;
	}

	/**
	 * Returns the target site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target site ID.
	 */
	public function get_target_site_id() {

		return $this->target_site_id;
	}

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see FilterableTranslation::get_type}.
	 *
	 * @return string
	 */
	public function get_page_type() {

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Common\Type\FilterableTranslation::get_type'
		);

		return $this->get_type();
	}

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see FilterableTranslation::get_remote_title}.
	 *
	 * @return string
	 */
	public function get_target_title() {

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Common\Type\FilterableTranslation::get_remote_title'
		);

		return $this->get_remote_title();
	}
}
