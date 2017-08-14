<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Term\ActiveTaxonomies;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox
 * @since   3.0.0
 */
final class SourceTermSaveContext implements \ArrayAccess {

	const SITE_ID = 'source_site_id';

	const TAXONOMY = 'taxonomy';

	const TERM_ID = 'term_id';

	const TERM = 'term';

	const TERM_PARENT = 'term_parent';

	const RELATED_BLOGS = 'related_blogs';

	/**
	 * @var \SplObjectStorage
	 */
	private static $contexts;

	/**
	 * @var \WP_Term
	 */
	private $term;

	/**
	 * @var ActiveTaxonomies
	 */
	private $active_taxonomies;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var ServerRequest
	 */
	private $request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param \WP_Term         $term           Post object.
	 * @param ActiveTaxonomies $active_taxonomies
	 * @param SiteRelations    $site_relations Site relations object.
	 * @param ServerRequest    $request        Server request object.
	 */
	public function __construct(
		\WP_Term $term,
		ActiveTaxonomies $active_taxonomies,
		SiteRelations $site_relations,
		ServerRequest $request
	) {

		$this->term = $term;

		$this->active_taxonomies = $active_taxonomies;

		$this->site_relations = $site_relations;

		$this->request = $request;

		if ( ! self::$contexts ) {
			self::$contexts = new \SplObjectStorage();
		}
	}

	/**
	 * @return array
	 */
	public function to_array(): array {

		if ( self::$contexts->contains( $this->term ) ) {
			return self::$contexts->offsetGet( $this->term );
		}

		$empty_context = [
			self::SITE_ID       => 0,
			self::TAXONOMY      => '',
			self::TERM_ID       => 0,
			self::TERM_PARENT   => 0,
			self::TERM          => new \WP_Term( new \stdClass() ),
			self::RELATED_BLOGS => [],
		];

		$source_site_id = (int) get_current_blog_id();

		$related_blogs = $this->site_relations->get_related_site_ids( $source_site_id );
		if ( empty( $related_blogs ) ) {
			self::$contexts->attach( $this->term, $empty_context );

			return $empty_context;
		}

		$taxonomy = $this->term->taxonomy;

		if ( ! taxonomy_exists( $taxonomy ) || ! $this->active_taxonomies->includes( $taxonomy ) ) {
			self::$contexts->attach( $this->term, $empty_context );

			return $empty_context;
		}

		// TODO: Content relations are using TERM TAXONOMY ID (instead of TERM ID). Check if this is what we want!?!
		$request_term_id = (int) $this->request->body_value( 'tag_ID', INPUT_REQUEST, FILTER_SANITIZE_NUMBER_INT );

		$context = [
			self::SITE_ID       => $source_site_id,
			self::TAXONOMY      => $taxonomy,
			self::TERM_ID       => $request_term_id ?: (int) $this->term->term_id,
			self::TERM_PARENT   => (int) $this->term->parent,
			self::TERM          => $this->term,
			self::RELATED_BLOGS => $related_blogs,
		];

		self::$contexts->attach( $this->term, $context );

		return $context;
	}

	/**
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {

		return array_key_exists( $offset, $this->to_array() );
	}

	/**
	 * @param string $offset
	 *
	 * @return int|string|array|null
	 */
	public function offsetGet( $offset ) {

		return $this->to_array()[ $offset ] ?? null;
	}

	/**
	 * Disabled.
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet( $offset, $value ) {

		throw new \BadMethodCallException( sprintf( '%s is immutable.', __CLASS__ ) );
	}

	/**
	 * Disabled.
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset( $offset ) {

		throw new \BadMethodCallException( sprintf( '%s is immutable.', __CLASS__ ) );
	}
}
