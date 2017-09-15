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
	 * @var array[]
	 */
	private $tags = array(
		'input' => array(
			'id'    => true,
			'name'  => true,
			'type'  => true,
			'value' => true,
		),
		'label' => array(
			'for' => true,
		),
		'li'    => array(),
	);

	/**
	 * @param Mlp_Relationship_Control_Data $data
	 */
	public function __construct( Mlp_Relationship_Control_Data $data ) {

		$this->data = $data;
	}

	public function get_formatted_results() {

		$results = $this->data->get_search_results();

		return $this->format_results( $results );
	}

	public function render() {

		echo wp_kses( $this->get_formatted_results(), $this->tags );
	}

	public function send_response() {

		wp_send_json_success( array(
			'html'         => $this->get_formatted_results(),
			'remoteSiteID' => $this->data->get_remote_site_id(),
		) );
	}

	public function show_search_results() {

		$this->render();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			mlp_exit();
		}
	}

	/**
	 * @param array $results
	 * @return string
	 */
	private function format_results( array $results ) {

		if ( empty( $results ) ) {
			return '<li>'
			. esc_html__( 'Nothing found.', 'multilingual-press' )
			. '</li>';
		}

		$out      = '';
		$site_id  = $this->data->get_remote_site_id();
		$results  = $this->prepare_titles( $results );

		/** @var WP_Post $result */
		foreach ( $results as $result ) {
			$out .= sprintf(
				'<li><label for="%4$s"><input type="radio" name="%2$s" value="%3$d" id="%4$s">%1$s</label></li>',
				esc_html( "{$result->post_title} (" . $this->get_translated_status( $result->post_status ) . ')' ),
				esc_attr( "mlp_add_post[{$site_id}]" ),
				esc_attr( $result->ID ),
				esc_attr( "id_{$site_id}_{$result->ID}" )
			);
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

		static $statuses = false;

		if ( ! $statuses ) {
			$statuses = get_post_statuses();
		}

		if ( isset( $statuses[ $status ] ) ) {
			return $statuses[ $status ];
		}

		return esc_html( ucfirst( $status ) );
	}

	/**
	 * Mark duplicates titles with the post ID.
	 *
	 * @param  array $posts
	 * @return array
	 */
	private function prepare_titles( array $posts ) {

		$out        = array();
		$titles     = array();
		$duplicates = array();

		/** @var WP_Post $post */
		foreach ( $posts as $post ) {

			$post->post_title = esc_html( $post->post_title );
			$existing         = array_search( $post->post_title, $titles, true );

			if ( $existing ) {
				$duplicates[] = $post->ID;
				$duplicates[] = $existing;
			}

			$out[ $post->ID ]    = $post;
			$titles[ $post->ID ] = $post->post_title;
		}

		if ( empty( $duplicates ) ) {
			return $out;
		}

		$duplicates = array_unique( $duplicates );

		foreach ( $duplicates as $id ) {
			$out[ $id ]->post_title = $out[ $id ]->post_title . ' [#' . $id . ']';
		}

		return $out;
	}
}
