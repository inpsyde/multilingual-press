<?php
/**
 * Request validation for the action 'save_post'.
 *
 * @author  toscho
 * @version 2014.03.09
 * @license MIT
 */
class Mlp_Save_Post_Request_Validator implements Mlp_Request_Validator_Interface {

	/**
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce;

	/**
	 * Constructor.
	 *
	 * @param Inpsyde_Nonce_Validator_Interface $nonce
	 */
	public function __construct( Inpsyde_Nonce_Validator_Interface $nonce ) {

		$this->nonce = $nonce;
	}

	/**
	 * Is this a valid request?
	 *
	 * @param  int $context Post id
	 * @return bool
	 */
	public function is_valid( $context = null ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( $this->is_real_revision( $context ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $context ) ) {
			return false;
		}

		return $this->nonce->is_valid();
	}

	/**
	 * Check post status.
	 *
	 * Includes special hacks for auto-drafts.
	 *
	 * @param  int $post_id
	 * @return bool
	 */
	private function is_real_revision( $post_id ) {

		if ( ! wp_is_post_revision( $post_id ) ) {
			false;
		}

		$post = get_post( $post_id );

		/* Auto-drafts are sent as revision with a status 'inherit'.
		 * We have to inspect the $_POST array to distinguish them from real
		 * revisions and attachments (which have the same status)
		 */

		if ( 'inherit' !== $post->post_status ) {
			return false;
		}

		if ( 'revision' !== $post->post_type ) {
			return false;
		}

		return 'auto-draft' !== filter_input( INPUT_POST, 'original_post_status' );
	}
}
