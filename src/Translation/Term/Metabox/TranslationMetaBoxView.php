<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBox;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxView;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Term\TermMetaBoxView;

use function Inpsyde\MultilingualPress\get_site_language;

/**
 * Meta box view implementation for term translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term\MetaBox
 * @since   3.0.0
 */
final class TranslationMetaBoxView implements TermMetaBoxView {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_RENDER_PREFIX = 'multilingualpress.term_translation_meta_box_';

	/**
	 * Position name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const POSITION_BOTTOM = 'bottom';

	/**
	 * Position name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const POSITION_MAIN = 'main';

	/**
	 * Position name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const POSITION_TOP = 'top';

	/**
	 * Position names.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
	const POSITIONS = [
		self::POSITION_TOP,
		self::POSITION_MAIN,
		self::POSITION_BOTTOM,
	];

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * @var \WP_Term
	 */
	private $source_term;

	/**
	 * @var \WP_Term
	 */
	private $remote_term;

	/**
	 * @var int
	 */
	private $remote_site_id;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int      $remote_site_id Site ID.
	 * @param \WP_Term $remote_term    Optional. Remote term object. Defaults to null.
	 */
	public function __construct( int $remote_site_id, \WP_Term $remote_term = null ) {

		$this->remote_site_id = $remote_site_id;

		$this->remote_term = $remote_term;
	}

	/**
	 * Returns an instance with the given data.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Data to be set.
	 *
	 * @return MetaBoxView
	 */
	public function with_data( array $data ): MetaBoxView {

		$clone = clone $this;

		$clone->data = array_merge( $this->data, $data );

		return $clone;
	}

	/**
	 * Returns an instance with the given term.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Term $source_term Term object to set.
	 *
	 * @return TermMetaBoxView
	 */
	public function with_term( \WP_Term $source_term ): TermMetaBoxView {

		$clone = clone $this;

		$clone->source_term = $source_term;

		return $clone;
	}

	/**
	 * Returns the rendered HTML.
	 *
	 * @since 3.0.0
	 *
	 * @return string Rendered HTML.
	 */
	public function render(): string {

		if ( ! $this->source_term ) {
			return '';
		}

		$title = ( $this->data['meta_box'] ?? null ) instanceof MetaBox ? $this->data['meta_box']->title() : '';

		list( $open, $close ) = $this->wrap_markup( ! empty( $this->data ), $title );

		$args = [
			$this->source_term,
			$this->remote_site_id,
			get_site_language( $this->remote_site_id ),
			$this->remote_term,
			$this->data,
		];

		ob_start();

		/**
		 * Fires right before the main content of the meta box.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term      $term                 Source term object.
		 * @param int           $remote_site_id       Remote site ID.
		 * @param string        $remote_site_language Remote site language.
		 * @param \WP_Term|null $remote_term          Remote term object.
		 * @param array         $data                 Data to be used by the view.
		 */
		do_action( self::ACTION_RENDER_PREFIX . self::POSITION_TOP, ...$args );

		/**
		 * Fires along with the main content of the meta box.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term      $term                 Source term object.
		 * @param int           $remote_site_id       Remote site ID.
		 * @param string        $remote_site_language Remote site language.
		 * @param \WP_Term|null $remote_term          Remote term object.
		 * @param array         $data                 Data to be used by the view.
		 */
		do_action( self::ACTION_RENDER_PREFIX . self::POSITION_MAIN, ...$args );

		/**
		 * Fires right after the main content of the meta box.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Term      $term                 Source term object.
		 * @param int           $remote_site_id       Remote site ID.
		 * @param string        $remote_site_language Remote site language.
		 * @param \WP_Term|null $remote_term          Remote term object.
		 * @param array         $data                 Data to be used by the view.
		 */
		do_action( self::ACTION_RENDER_PREFIX . self::POSITION_BOTTOM, ...$args );

		$markup = ob_get_clean();

		return $open . $markup . $close;
	}

	/**
	 * @param bool   $update
	 * @param string $title
	 *
	 * @return array
	 */
	private function wrap_markup( bool $update, string $title ): array {

		if ( $update ) {
			$opening = '<tr class="form-field"><th scope="row">%s</th><td>';
			$closing = '</td></tr>';
		} else {
			$opening = '<div class="form-field" style="padding: 2em 0;"><strong>%s</strong>';
			$closing = '</div>';
		}

		return [
			sprintf( $opening, $title ),
			$closing,
		];
	}
}
