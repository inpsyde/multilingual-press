<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\Common\BasePathAdapter;
use Inpsyde\MultilingualPress\Database\TableStringReplacer;

/**
 * Attachment copier implementation respecting attachments referenced in the post meta table only.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
final class WPDBAttachmentCopier implements AttachmentCopier {

	/**
	 * @var BasePathAdapter
	 */
	private $base_path_adapter;

	/**
	 * @var bool
	 */
	private $copied_files;

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * @var TableStringReplacer
	 */
	private $table_string_replacer;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param \wpdb               $db                    WordPress database object.
	 * @param BasePathAdapter     $base_path_adapter     Base path adapter object.
	 * @param TableStringReplacer $table_string_replacer Table string replacer object.
	 */
	public function __construct(
		\wpdb $db,
		BasePathAdapter $base_path_adapter,
		TableStringReplacer $table_string_replacer
	) {

		$this->db = $db;

		$this->base_path_adapter = $base_path_adapter;

		$this->table_string_replacer = $table_string_replacer;
	}

	/**
	 * Copies all attachment files of the site with given ID to the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $source_site_id Source site ID.
	 *
	 * @return bool Whether or not any attachment files were copied.
	 */
	public function copy_attachments( int $source_site_id ): bool {

		$destination_dir = $this->base_path_adapter->basedir();

		switch_to_blog( $source_site_id );

		$source_dir = $this->base_path_adapter->basedir();

		if ( ! ( is_dir( $source_dir ) && is_readable( $source_dir ) ) ) {
			restore_current_blog();

			return false;
		}

		$attachment_paths = $this->get_attachment_paths();
		if ( ! $attachment_paths ) {
			restore_current_blog();

			return false;
		}

		$this->copied_files = false;

		array_walk( $attachment_paths, function ( array $paths, $dir ) use ( $source_dir, $destination_dir ) {

			$this->copy_dir( $paths, "$source_dir/$dir", "$destination_dir/$dir" );
		} );

		if ( $this->copied_files ) {
			$source_url = $this->base_path_adapter->baseurl();

			restore_current_blog();

			$this->update_attachment_urls( $source_url, $this->base_path_adapter->baseurl() );

			return true;
		}

		restore_current_blog();

		return false;
	}

	/**
	 * Extracts all registered attachment paths from the database.
	 *
	 * Only files referenced in the database are trustworthy, and will therefore get copied.
	 *
	 * @return string[] The array with directories relative to uploads as keys, and arrays of file paths as values.
	 */
	private function get_attachment_paths(): array {

		$metadata = $this->db->get_results(
			"SELECT meta_value FROM {$this->db->postmeta} WHERE meta_key = '_wp_attachment_metadata'"
		);

		if ( ! $metadata ) {
			return [];
		}

		$attachment_paths = array_reduce( $metadata, function ( array $attachment_paths, $metadata ) {

			$meta_value = maybe_unserialize( $metadata->meta_value );
			if ( empty( $meta_value['file'] ) ) {
				return $attachment_paths;
			}

			$file = $meta_value['file'];

			$dir = dirname( $file );

			$attachment_paths[ $dir ][] = basename( $file );

			if ( empty( $meta_value['sizes'] ) ) {
				return $attachment_paths;
			}

			$sizes = $meta_value['sizes'];

			$attachment_paths[ $dir ] = array_reduce( $sizes, function ( array $dirs, array $size ) {

				$dirs[] = $size['file'];

				return $dirs;
			}, $attachment_paths[ $dir ] );

			return $attachment_paths;
		}, [] );

		return $attachment_paths;
	}

	/**
	 * Copies all given files from one site to another.
	 *
	 * @param string[] $paths           List of file paths relative to the given source directory.
	 * @param string   $source_dir      Full source directory path.
	 * @param string   $destination_dir Full destination directory path.
	 *
	 * @return void
	 */
	private function copy_dir( array $paths, string $source_dir, string $destination_dir ) {

		if ( ! is_dir( $source_dir ) ) {
			return;
		}

		if ( ! is_dir( $destination_dir ) && ! wp_mkdir_p( $destination_dir ) ) {
			return;
		}

		array_walk( $paths, function ( $path ) use ( $source_dir, $destination_dir ) {

			if (
				file_exists( "{$source_dir}/{$path}" )
				&& ! file_exists( "{$destination_dir}/{$path}" )
				&& copy( "{$source_dir}/{$path}", "{$destination_dir}/{$path}" )
			) {
				$this->copied_files = true;
			}
		} );
	}

	/**
	 * Updates attachment URLs according to the given arguments
	 *
	 * @param string $source_url      Source site uploads url.
	 * @param string $destination_url Destination site uploads url.
	 *
	 * @return void
	 */
	private function update_attachment_urls( string $source_url, string $destination_url ) {

		$tables = [
			$this->db->comments      => [
				'comment_content',
			],
			$this->db->posts         => [
				'guid',
				'post_content',
				'post_content_filtered',
				'post_excerpt',
			],
			$this->db->term_taxonomy => [
				'description',
			],
		];

		array_walk( $tables, function ( array $columns, $table ) use ( $source_url, $destination_url ) {

			$this->table_string_replacer->replace_string(
				$table,
				$columns,
				$source_url,
				$destination_url
			);
		} );
	}
}
