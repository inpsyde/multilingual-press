<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\NetworkState;
use Inpsyde\MultilingualPress\Factory\NonceFactory;

use function Inpsyde\MultilingualPress\site_exists;

/**
 * Data model for advanced post translation. Handles inserts and updates.
 */
class Mlp_Advanced_Translator_Data implements Mlp_Advanced_Translator_Data_Interface, Mlp_Save_Post_Interface {

	/**
	 * @var array
	 */
	private $allowed_post_types;

	/**
	 * @var Mlp_Translatable_Post_Data_Interface
	 */
	private $basic_data;

	/**
	 * @var string
	 */
	private $id_base = 'mlp-translation-data';

	/**
	 * @var string
	 */
	private $name_base = 'mlp_translation_data';

	/**
	 * @var NonceFactory
	 */
	private $nonce_factory;

	/**
	 * @var array
	 */
	private $post_request_data = [];

	/**
	 * @var SiteRelations
	 */
	private $relations;

	/**
	 * Context for hook on save.
	 *
	 * @var array
	 */
	private $save_context = [];

	/**
	 * @param                                      $deprecated
	 * @param Mlp_Translatable_Post_Data_Interface $basic_data
	 * @param array                                $allowed_post_types
	 * @param SiteRelations                        $relations
	 * @param NonceFactory                         $nonce_factory Nonce factory object.
	 */
	public function __construct(
		$deprecated,
		Mlp_Translatable_Post_Data_Interface $basic_data,
		array $allowed_post_types,
		SiteRelations $relations,
		NonceFactory $nonce_factory
	) {

		$this->basic_data = $basic_data;

		$this->allowed_post_types = $allowed_post_types;

		$this->relations = $relations;

		$this->nonce_factory = $nonce_factory;

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$this->post_request_data = $_POST;
		}
	}

	/**
	 * Base string for name attribute in translation view.
	 *
	 * @return string
	 */
	public function get_name_base() {

		return $this->name_base;
	}

	/**
	 * Base string for ID attribute in translation view.
	 *
	 * @return string
	 */
	public function get_id_base() {

		return $this->id_base;
	}

	/**
	 * Save the post to the blogs
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function save( $post_id, WP_Post $post ) {

		if ( ! $this->basic_data->is_valid_save_request( $post, $this->name_base ) ) {
			return;
		}

		// TODO: Check if this is what we want to do here (use site relations vs. sites with a language set)...
		$available_blogs = get_site_option( 'inpsyde_multilingual' );
		if ( empty( $available_blogs ) ) {
			return;
		}

		// auto-drafts
		$post_id   = $this->basic_data->get_real_post_id( $post_id );
		$post_type = $this->basic_data->get_real_post_type( $post );

		$source_blog_id = (int) get_current_blog_id();

		$featured_image_path = $this->get_featured_image_path( $post_id );

		$related_blogs = $this->relations->get_related_site_ids( $source_blog_id );
		if ( empty( $related_blogs ) ) {
			return;
		}

		// Check Post Type
		if ( ! in_array( $post_type, $this->allowed_post_types, true ) ) {
			return;
		}

		$this->save_context = [
			'source_blog'    => get_current_blog_id(),
			'source_post'    => $post,
			'real_post_type' => $post_type,
			'real_post_id'   => $post_id,
		];

		$this->basic_data->set_save_context( $this->save_context );

		$post_meta = $this->basic_data->get_post_meta_to_transfer();

		$this->basic_data->find_post_parents( $post_type, $post->post_parent );

		/**
		 * Runs before the first save_post action is called for the remote blogs.
		 *
		 * @param array $save_context Context of the to-be-saved post.
		 */
		do_action( 'mlp_before_post_synchronization', $this->save_context );

		// TODO: Fire also a typeless action that has the type (i.e., post) as second argument.

		$network_state = NetworkState::from_globals();

		foreach ( $this->post_request_data[ $this->name_base ] as $remote_blog_id => $post_data ) {
			if (
				! in_array( $remote_blog_id, $related_blogs )
				|| ! site_exists( $remote_blog_id )
			) {
				continue;
			}

			$nonce = $this->nonce_factory->create( [
				"save_translation_of_post_{$post_id}_for_site_{$remote_blog_id}",
			] );

			$request_validator = new Mlp_Save_Post_Request_Validator( $nonce );
			if ( ! $request_validator->is_valid( $post ) ) {
				continue;
			}

			switch_to_blog( $remote_blog_id );

			$this->save_context['target_blog_id'] = $remote_blog_id;

			$new_post = $this->create_post_to_send( $post_data, $post_type, $remote_blog_id );

			if ( [] !== $new_post ) {
				$sync_thumb = ! empty( $post_data['thumbnail'] );
				$update     = ! empty( $post_data['remote_post_id'] ) && 0 < $post_data['remote_post_id'];
				$new_id     = $this->sync_post( $new_post, $post_id, $remote_blog_id, $update );

				$this->basic_data->set_save_context( $this->save_context );

				$this->basic_data->update_remote_post_meta( $new_id, $post_meta );

				if ( $sync_thumb && $featured_image_path ) {
					$this->copy_thumb( $new_id, $featured_image_path );
				}

				$tax_data = empty( $post_data['tax'] ) ? [] : (array) $post_data['tax'];
				$this->set_remote_tax_terms( $new_id, $tax_data );
			}
		}

		$network_state->restore();

		/**
		 * Runs after all save_post actions have been called for the remote blogs.
		 *
		 * @param array $save_context
		 */
		do_action( 'mlp_after_post_synchronization', $this->save_context );
	}

	/**
	 * Wrapper for get_taxonomies_with_terms( $post ).
	 *
	 * Wraps the call into a blog switch.
	 *
	 * @param WP_Post $post
	 * @param int     $blog_id
	 *
	 * @return array
	 */
	public function get_taxonomies( WP_Post $post, $blog_id ) {

		switch_to_blog( $blog_id );
		$out = $this->get_taxonomies_with_terms( $post );
		restore_current_blog();

		return $out;
	}

	/**
	 * Insert the (updated) post.
	 *
	 * @param array $new_post
	 * @param int   $post_id
	 * @param int   $remote_blog_id
	 * @param bool  $update
	 *
	 * @return int|WP_Error
	 */
	private function sync_post(
		array $new_post,
		$post_id,
		$remote_blog_id,
		$update
	) {

		if ( $update ) {
			return wp_update_post( $new_post );
		}

		$new_id = wp_insert_post( $new_post );

		$this->basic_data->set_linked_element( $post_id, $remote_blog_id, $new_id );

		return $new_id;
	}

	/**
	 * Copy the featured image.
	 *
	 * @param int    $new_id
	 * @param string $featured_image_path
	 *
	 * @return bool     TRUE on success, FALSE when the image could not be copied.
	 */
	private function copy_thumb( $new_id, $featured_image_path ) {

		// Prepare and Copy the image
		$filedir = wp_upload_dir();

		if ( ! is_dir( $filedir['path'] ) and ! wp_mkdir_p( $filedir['path'] ) ) {
			// failed to make the directory
			return false;
		}

		$filename = wp_unique_filename( $filedir['path'], basename( $featured_image_path ) );
		$copy     = copy( $featured_image_path, $filedir['path'] . '/' . $filename );

		// Now insert it into the posts
		if ( ! $copy ) {
			// failed to write the file
			return false;
		}

		$wp_filetype = wp_check_filetype( $filedir['url'] . '/' . $filename );
		$attachment  = [
			'post_mime_type' => $wp_filetype['type'],
			'guid'           => $filedir['url'] . '/' . $filename,
			'post_parent'    => $new_id,
			'post_title'     => '',
			'post_excerpt'   => '',
			'post_author'    => get_current_user_id(),
			'post_content'   => '',
		];

		$full_path = $filedir['path'] . '/' . $filename;
		$attach_id = wp_insert_attachment( $attachment, $full_path );

		// Everything went well?
		if ( is_wp_error( $attach_id ) ) {
			// failed to insert the image
			return false;
		}

		wp_update_attachment_metadata(
			$attach_id,
			wp_generate_attachment_metadata( $attach_id, $full_path )
		);

		return update_post_meta( $new_id, '_thumbnail_id', $attach_id );
	}

	/**
	 * Fetch data of original featured image.
	 *
	 * @param  int $post_id
	 *
	 * @return string
	 */
	private function get_featured_image_path( $post_id ) {

		if ( ! has_post_thumbnail( $post_id ) ) {
			return '';
		}

		$meta = wp_get_attachment_metadata( get_post_thumbnail_id( $post_id ) );

		$filedir = wp_upload_dir();

		return $filedir['basedir'] . '/' . $meta['file'];
	}

	/**
	 * Create the default post data for the save() method.
	 *
	 * @param  array  $post_data Post data.
	 * @param  string $post_type Post type.
	 * @param  int    $blog_id   Blog ID.
	 *
	 * @return array
	 */
	private function create_post_to_send( array $post_data, $post_type, $blog_id ) {

		$title   = $this->get_remote_post_title( $post_data );
		$name    = $this->get_remote_post_name( $post_data );
		$content = $this->get_remote_post_content( $post_data );
		$excerpt = $this->get_remote_post_excerpt( $post_data );

		if ( $this->is_empty_remote_post( $title, $content, $post_type ) ) {
			return [];
		}

		$new_post_data = [
			'post_type'    => $post_type,
			'post_title'   => $title,
			'post_name'    => $name,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
			'post_parent'  => $this->basic_data->get_post_parent( $blog_id ),
		];

		if ( ! empty( $post_data['remote_post_id'] ) ) {
			$new_post_data['ID'] = $post_data['remote_post_id'];

			/**
			 * Filter the post data before saving the post.
			 *
			 * @param array $post_data    Post data.
			 * @param array $save_context Context of the to-be-saved post.
			 */
			$new_post_data = apply_filters( 'mlp_pre_save_post', $new_post_data, $this->save_context );

			return $new_post_data;
		}

		// new post
		$new_post_data['post_status'] = apply_filters('mlp_save_translation_status','draft');

		// add post_author if override is available
		if ( isset( $this->post_request_data['post_author_override'] ) ) {
			$new_post_data['post_author'] = $this->post_request_data['post_author_override'];
		}

		/**
		 * Filter the new post data before inserting the post into the database.
		 *
		 * @param array $post_data    Post data.
		 * @param array $save_context Context of the to-be-saved post.
		 */
		$new_post_data = apply_filters( 'mlp_pre_insert_post', $new_post_data, $this->save_context );

		return $new_post_data;
	}

	/**
	 * Check if there actually is content in the translation. Prevents creation of emptytranslation drafts.
	 *
	 * @param string $title     Post title.
	 * @param string $content   Post content.
	 * @param string $post_type Post type.
	 *
	 * @return bool
	 */
	private function is_empty_remote_post( $title, $content, $post_type ) {

		if (
			'' !== $title
			&& '' !== $content
		) {
			return false;
		}

		if (
			post_type_supports( $post_type, 'title' )
			&& '' !== $title
		) {
			return false;
		}

		if (
			post_type_supports( $post_type, 'editor' )
			&& '' !== $content
		) {
			return false;
		}

		return true;
	}

	/**
	 * Get all existing taxonomies for the given post, including existing terms.
	 *
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	private function get_taxonomies_with_terms( WP_Post $post ) {

		$out = [];

		$taxonomies = get_object_taxonomies( $post, 'objects' );

		foreach ( $taxonomies as $taxonomy => $properties ) {

			// Don't show what the user cannot change.
			if ( ! current_user_can( $properties->cap->assign_terms, $taxonomy ) ) {
				continue;
			}

			$terms = get_terms( $taxonomy, [ 'hide_empty' => false ] );

			// we do not allow creating new terms
			if ( empty( $terms ) ) {
				continue;
			}

			$terms = $this->set_active_terms( $terms, $taxonomy, $post );

			if ( $this->taxonomy_is_mutually_exclusive( $taxonomy ) ) {
				$out['exclusive'][ $taxonomy ] = [
					'properties' => $properties,
					'terms'      => $terms,
				 ];
			} else {
				$out['inclusive'][ $taxonomy ] = [
					'properties' => $properties,
					'terms'      => $terms,
				 ];
			}
		}

		return $out;
	}

	/**
	 * Update terms for each taxonomy.
	 *
	 * This operates in the context of the target blog, after switch_to_blog().
	 *
	 * @param  int   $new_id
	 * @param  array $tax_data
	 *
	 * @return bool TRUE on complete success, FALSE when there were errors.
	 */
	private function set_remote_tax_terms( $new_id, array $tax_data ) {

		$errors = [];

		$post = get_post( $new_id );

		$taxonomies = get_object_taxonomies( $post, 'objects' );
		foreach ( $taxonomies as $taxonomy => $properties ) {
			if ( ! current_user_can( $properties->cap->assign_terms, $taxonomy ) ) {
				continue;
			}

			$terms = [];

			$term_ids = empty( $tax_data[ $taxonomy ] ) ? [] : (array) $tax_data[ $taxonomy ];
			foreach ( $term_ids as $term_id ) {
				$term = get_term_by( 'id', (int) $term_id, $taxonomy );
				if ( $term ) {
					$terms[] = $term->term_id;
				}
			}

			$set = wp_set_object_terms( $new_id, $terms, $taxonomy );
			if ( is_wp_error( $set ) ) {
				$errors[ $taxonomy ] = $set;
			}
		}

		return empty( $errors );
	}

	/**
	 * Prepare the title for the post we want to synchronize.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	private function get_remote_post_title( array $data ) {

		if ( isset ( $data['title'] ) ) {
			return $data['title'];
		}

		if ( isset ( $this->post_request_data['post_title'] ) ) {
			return (string) $this->post_request_data['post_title'];
		}

		return '';
	}

	/**
	 * Prepare the title for the post we want to synchronize.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	private function get_remote_post_name( array $data ) {

		if ( isset ( $data['name'] ) ) {
			return $data['name'];
		}

		if ( isset ( $this->post_request_data['post_name'] ) ) {
			return (string) $this->post_request_data['post_name'];
		}

		return '';
	}

	/**
	 * Prepare the content for the post we want to synchronize.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	private function get_remote_post_content( array $data ) {

		if ( isset ( $data['content'] ) ) {
			return $data['content'];
		}

		if ( isset ( $this->post_request_data['post_content'] ) ) {
			return (string) $this->post_request_data['post_content'];
		}

		return '';
	}

	/**
	 * Prepare the excerpt for the post we want to synchronize.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	private function get_remote_post_excerpt( array $data ) {

		if ( isset( $data['excerpt'] ) ) {
			return $data['excerpt'];
		}

		if ( isset( $this->post_request_data['post_excerpt'] ) ) {
			return (string) $this->post_request_data['post_excerpt'];
		}

		return '';
	}

	/**
	 * Checks if more than one term can be assigned to a taxonomy.
	 *
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return bool
	 */
	private function taxonomy_is_mutually_exclusive( $taxonomy ) {

		/**
		 * Filter mutually exclusive taxonomies.
		 *
		 * @param string[] $taxonomies Mutually exclusive taxonomy names.
		 */
		$exclusive = apply_filters( 'mlp_mutually_exclusive_taxonomies', [ 'post_format' ] );

		return in_array( $taxonomy, $exclusive, true );
	}

	/**
	 * Mark active terms for the post.
	 *
	 * @param array   $terms
	 * @param string  $taxonomy
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	private function set_active_terms( array $terms, $taxonomy, WP_Post $post ) {

		$out = [];

		foreach ( $terms as $term ) {
			$term->active = has_term( $term->term_id, $taxonomy, $post );
			$out[]        = $term;
		}

		return $out;
	}
}
