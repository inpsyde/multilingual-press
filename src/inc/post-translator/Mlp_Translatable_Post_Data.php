<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Factory\NonceFactory;

use function Inpsyde\MultilingualPress\get_translation_ids;

/**
 * Data model for post translation. Handles inserts of new posts only.
 */
class Mlp_Translatable_Post_Data {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var string
	 */
	private $name_base = 'mlp_to_translate';

	/**
	 * @var array
	 */
	private $parent_elements = [];

	/**
	 * @var array
	 */
	private $post_request_data = [];

	/**
	 * @var array
	 */
	public $save_context = [];

	/**
	 * @var int
	 */
	private $source_site_id;

	/**
	 * @param                  $deprecated
	 * @param array            $allowed_post_types
	 * @param string           $link_table
	 * @param ContentRelations $content_relations
	 * @param NonceFactory     $nonce_factory      Nonce factory object.
	 */
	function __construct(
		$deprecated,
		array $allowed_post_types,
		$link_table,
		ContentRelations $content_relations,
		NonceFactory $nonce_factory
	) {

		$this->content_relations = $content_relations;

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$this->post_request_data = $_POST;
		}

		$this->source_site_id = get_current_blog_id();
	}

	public function save() {

		// BAIL if not is valid save request!

		// Set post ID to the real one.

		// Set up save context array.

		// Set up and filter post data array, and BAIL if invalid!

		// Set up post meta array.

		// Set up post thumbnail data.

		// Set up new post array (with draft status and the rest according to the original post.

		// Set up remote post parents.

		// Fire mlp_before_post_synchronization action.

		foreach ( $this->post_request_data[ $this->name_base ] as $site_id ) {

			// Update target site ID in save context array.

			// Save remote post.

			// Set post meta.

			// Set post thumbnail.

			// Set up post relatinoship.

		}

		// Fire mlp_after_post_synchronization action.
	}

	/**
	 * @param string $post_type
	 * @param int    $post_parent
	 *
	 * @return void
	 */
	public function find_post_parents( $post_type, $post_parent ) {

		if ( ! is_post_type_hierarchical( $post_type ) ) {
			return;
		}

		if ( 0 < $post_parent ) {
			$this->parent_elements = get_translation_ids( $post_parent );
		}
	}

	/**
	 * @param $blog_id
	 *
	 * @return int
	 */
	public function get_post_parent( $blog_id ) {

		if ( empty( $this->parent_elements ) ) {
			return 0;
		}

		if ( empty( $this->parent_elements[ $blog_id ] ) ) {
			return 0;
		}

		return $this->parent_elements[ $blog_id ];
	}

	/**
	 * Figure out the post ID.
	 *
	 * Inspects POST request data and too, because we get two IDs on auto-drafts.
	 *
	 * @param  int $post_id
	 *
	 * @return int
	 */
	public function get_real_post_id( $post_id ) {

		if ( ! empty( $this->post_request_data['post_ID'] ) ) {
			return (int) $this->post_request_data['post_ID'];
		}

		return $post_id;
	}

	/**
	 * set the source id of the element
	 *
	 * @param   int $source_content_id ID of current element
	 * @param   int $remote_site_id    ID of remote site
	 * @param   int $remote_content_id ID of remote content
	 *
	 * @return  void
	 */
	public function set_linked_element( $source_content_id, $remote_site_id, $remote_content_id ) {

		$this->content_relations->set_relation(
			$this->source_site_id,
			$remote_site_id,
			$source_content_id,
			$remote_content_id,
			'post'
		);
	}

	/**
	 * Add source post meta to remote post.
	 *
	 * @param  int   $remote_post_id
	 * @param  array $post_meta
	 *
	 * @return void
	 */
	public function update_remote_post_meta( $remote_post_id, $post_meta = [] ) {

		if ( empty( $post_meta ) ) {
			return;
		}

		/**
		 * Filter post meta data before saving.
		 *
		 * @param array $post_meta    Post meta data.
		 * @param array $save_context Context of the to-be-saved post.
		 */
		$new_post_meta = apply_filters( 'mlp_pre_insert_post_meta', $post_meta, $this->save_context );
		if ( empty( $new_post_meta ) ) {
			return;
		}

		foreach ( $new_post_meta as $key => $value ) {
			update_post_meta( $remote_post_id, $key, $value );
		}
	}

	/**
	 * Return filtered array of post meta data.
	 *
	 * This function has changed in version 2.1: In earlier versions, we have
	 * just used all available post meta keys. That raised too many
	 * compatibility issues with other plugins and some themes, so we use an
	 * empty array now. If you want to synchronize post meta data, you have to
	 * opt-in per filter.
	 *
	 * @return array
	 */
	public function get_post_meta_to_transfer() {

		/**
		 * Filter the to-be-synchronized post meta fields.
		 *
		 * @param array $post_meta    Post meta fields.
		 * @param array $save_context Context of the to-be-saved post.
		 */
		return apply_filters( 'mlp_pre_save_post_meta', [], $this->save_context );
	}

	/**
	 * Get the real current post type.
	 *
	 * Includes workaround for auto-drafts.
	 *
	 * @param  WP_Post $post
	 *
	 * @return string
	 */
	public function get_real_post_type( WP_Post $post ) {

		$post_id = $post->ID;

		static $post_type = [];
		if ( isset( $post_type[ $post_id ] ) ) {
			return $post_type[ $post_id ];
		}

		if (
			'revision' === $post->post_type
			&& ! empty( $this->post_request_data['post_type'] )
			&& is_string( $this->post_request_data['post_type'] )
			&& 'revision' !== $this->post_request_data['post_type']
		) {
			$post_type[ $post_id ] = $this->post_request_data['post_type'];
		} else {
			$post_type[ $post_id ] = $post->post_type;
		}

		return $post_type[ $post_id ];
	}
}
