<?php
/**
 * Inpsyde_Nonce_Validator
 *
 * A very simple implementation to provide nonces and validation.
 *
 * @link  http://marketpress.com/2013/how-to-update-custom-fields-in-a-multi-site/
 * @version 2014.03.13
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Inpsyde_Nonce_Validator implements Inpsyde_Nonce_Validator_Interface {

	/**
	 * @var string
	 */
	private $base;

	/**
	 * Current nonce action.
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Current nonce name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $blog_id;

	/**
	 * Constructor.
	 *
	 * @param string $base
	 * @param int    $blog_id Set a blog id to restrict the nonce to one site.
	 */
	public function __construct( $base, $blog_id = 0 )
	{
		$this->base    = (string) $base;
		$this->blog_id = absint( $blog_id );
		$this->name    = $this->base . '_nonce_name_'   . $this->blog_id;
		$this->action  = $this->base . '_nonce_action_' . $this->blog_id;
	}

	/**
	 * Get nonce field name.
	 *
	 * @return string
	 */
	public function get_name()
	{
		return $this->name;
	}

	/**
	 * Get nonce action.
	 *
	 * @return string
	 */
	public function get_action()
	{
		return $this->action;
	}

	/**
	 * Verify request.
	 *
	 * @return bool
	 */
	public function is_valid()
	{
		if ( 0 === $this->blog_id )
			$blog_id = 0;
		else
			$blog_id = get_current_blog_id();

		$name = $this->base . '_nonce_name_' . $blog_id;

		if ( empty ( $_REQUEST[ $name ] ) )
			return FALSE;

		return wp_verify_nonce( $_REQUEST[ $name ], $this->action );
	}
}