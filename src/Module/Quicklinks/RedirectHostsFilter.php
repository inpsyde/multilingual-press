<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Quicklinks;

use Inpsyde\MultilingualPress\Common\ContextAwareFilter;
use Inpsyde\MultilingualPress\Common\Filter;

/**
 * Extends the allowed hosts for redirection.
 *
 * @package Inpsyde\MultilingualPress\Module\Quicklinks
 * @since   3.0.0
 */
final class RedirectHostsFilter implements Filter {

	use ContextAwareFilter;

	/**
	 * @var int
	 */
	private $accepted_args;

	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * @var string
	 */
	private $hook;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param \wpdb $db WordPress database object.
	 */
	public function __construct( \wpdb $db ) {

		$this->db = $db;

		$this->accepted_args = 2;

		$this->callback = [ $this, 'filter_hosts' ];

		$this->hook = 'allowed_redirect_hosts';
	}

	/**
	 * Adds all domains of the network to the list of allowed hosts.
	 *
	 * @since   3.0.0
	 * @wp-hook allowed_redirect_hosts
	 *
	 * @param string[] $home_hosts  Array with one entry: the host of home_url().
	 * @param string   $remote_host Host name of the URL to validate.
	 *
	 * @return string[] Filtered hosts.
	 */
	public function filter_hosts( array $home_hosts, string $remote_host ): array {

		// Network with sub directories.
		if ( in_array( $remote_host, $home_hosts, true ) ) {
			return $home_hosts;
		}

		$query = "
SELECT `domain`
FROM {$this->db->blogs}
WHERE site_id = %d
	AND `public` = '1'
	AND archived = '0'
	AND mature = '0'
	AND spam = '0'
	AND deleted = '0'
ORDER BY `domain` DESC";
		$query = $this->db->prepare( $query, $this->db->siteid );

		$domains = $this->db->get_col( $query );

		$allowed_hosts = array_unique( array_merge( $home_hosts, $domains ) );

		return $allowed_hosts;
	}
}
