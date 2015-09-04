<?php
/**
 * Class Mlp_Relationship_Control_Ajax_Search
 *
 * Render results from search data.
 *
 * @version 2014.02.17
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Relationship_Control_Ajax_Search {

	/**
	 * @var Mlp_Relationship_Control_Data
	 */
	private $data;

	/**
	 * @param Mlp_Relationship_Control_Data $data
	 */
	public function __construct( Mlp_Relationship_Control_Data $data ) {
		$this->data = $data;
	}

	public function render() {

		$results = $this->data->get_search_results();

		print $this->format_results( $results );

		if ( defined( 'DOING_AJAX') && DOING_AJAX )
			die;
	}

	public function show_search_results()
	{

		$results = $this->data->get_search_results();

		print $this->format_results( $results );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			die;
	}

	/**
	 * @param array $results
	 * @return string
	 */
	private function format_results( Array $results ) {

		if ( empty ( $results ) )
			return '<li>'
			. esc_html__( 'Nothing found.', 'multilingualpress' )
			. '</li>';

		$out      = '';
		$blog_id  = $this->data->get_remote_blog_id();
		$results  = $this->prepare_titles( $results );

		/** @var WP_Post $result */
		foreach ( $results as $result ) {

			$id     = "id_{$blog_id}_$result->ID";
			$name   = 'mlp_add_post[' . $blog_id . ']';
			$status = $this->get_translated_status( $result->post_status );

			$out .= "<li><label for='$id'>"
				. "<input type='radio' name='$name' value='$result->ID' id='$id'>"
				. $result->post_title
				. " ($status)"
				. '</label></li>';
		}

		return $out;
	}

	/**
	 * Get the translated post status if possible.
	 *
	 * @param  string $status
	 * @return string
	 */
	private function get_translated_status( $status ) {

		static $statuses = FALSE;

		! $statuses and $statuses = get_post_statuses();

		if ( isset( $statuses[ $status ] ) )
			return $statuses[ $status ];

		return esc_html( ucfirst( $status ) );
	}

	/**
	 * Mark duplicates titles with the post ID.
	 *
	 * @param  array $posts
	 * @return array
	 */
	private function prepare_titles( Array $posts ) {

		$out = $titles = $duplicates = array ();

		/** @var WP_Post $post */
		foreach ( $posts as $post ) {

			$post->post_title = esc_html( $post->post_title );
			$existing         = array_search( $post->post_title, $titles );

			if ( $existing ) {
				$duplicates[] = $post->ID;
				$duplicates[] = $existing;
			}

			$out[ $post->ID ]    = $post;
			$titles[ $post->ID ] = $post->post_title;
		}

		if ( empty ( $duplicates ) )
			return $out;

		$duplicates = array_unique( $duplicates );

		foreach ( $duplicates as $id )
			$out[ $id ]->post_title = $out[ $id ]->post_title . ' [#' . $id . ']';

		return $out;
	}
}