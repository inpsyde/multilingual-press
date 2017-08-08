<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Factory\NonceFactory;

/**
 * Data model for advanced post translation. Handles inserts and updates.
 */
class Mlp_Advanced_Translator_Data {

	/**
	 * @var array
	 */
	private $allowed_post_types;

	/**
	 * @var Mlp_Translatable_Post_Data
	 */
	private $basic_data;

	/**
	 * @var string
	 */
	private $featured_image_path = '';

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
	 * @var string
	 */
	private $post_type;

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
	 * @param                            $deprecated
	 * @param Mlp_Translatable_Post_Data $basic_data
	 * @param array                      $allowed_post_types
	 * @param SiteRelations              $relations
	 * @param NonceFactory               $nonce_factory Nonce factory object.
	 */
	public function __construct(
		$deprecated,
		Mlp_Translatable_Post_Data $basic_data,
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
	 * Save the post to the blogs
	 *
	 * @param int     $post_id
	 *
	 * @return void
	 */
	public function save( $post_id ) {

		// BAIL if not is valid save request!

		// Set both post ID and post type to the real ones.

		// Set up featured image path.

		// BAIL if post type is not allowed!

		// Set up save context array.

		// Set up post meta array.

		// Set up remote post parents.

		// Fire mlp_before_post_synchronization action.

		foreach ( $this->post_request_data[ $this->name_base ] as $remote_blog_id => $post_data ) {

			// Update target site ID in save context array.

			$new_post = $this->create_post_to_send( $post_data, $this->post_type, $remote_blog_id );
			if ( [] !== $new_post ) {
				$new_id = $this->sync_post(
					$new_post,
					$post_id,
					$remote_blog_id,
					! empty( $post_data['remote_post_id'] ) && 0 < $post_data['remote_post_id']
				);

				// Set post meta.

				if ( ! empty( $post_data['thumbnail'] ) ) {
					$this->copy_thumb( $new_id, $this->featured_image_path );
				}

				$this->set_remote_tax_terms( $new_id, empty( $post_data['tax'] ) ? [] : (array) $post_data['tax'] );
			}

		}

		// Fire mlp_after_post_synchronization action.
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
	 * @return void
	 */
	private function copy_thumb( $new_id, $featured_image_path ) {

		if ( ! $featured_image_path ) {
			return;
		}

		// Prepare and Copy the image
		$filedir = wp_upload_dir();

		if ( ! is_dir( $filedir['path'] ) and ! wp_mkdir_p( $filedir['path'] ) ) {
			// failed to make the directory
			return;
		}

		$filename = wp_unique_filename( $filedir['path'], basename( $featured_image_path ) );
		$copy     = copy( $featured_image_path, $filedir['path'] . '/' . $filename );

		// Now insert it into the posts
		if ( ! $copy ) {
			// failed to write the file
			return;
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
			return;
		}

		wp_update_attachment_metadata(
			$attach_id,
			wp_generate_attachment_metadata( $attach_id, $full_path )
		);

		update_post_meta( $new_id, '_thumbnail_id', $attach_id );
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

		$title = $this->get_remote_post_title( $post_data );

		$content = $this->get_remote_post_content( $post_data );

		if ( $this->is_empty_remote_post( $title, $content, $post_type ) ) {
			return [];
		}

		$new_post_data = [
			'post_type'    => $post_type,
			'post_title'   => $title,
			'post_name'    => $this->get_remote_post_name( $post_data ),
			'post_content' => $content,
			'post_excerpt' => $this->get_remote_post_excerpt( $post_data ),
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
		$new_post_data['post_status'] = 'draft';

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
}
