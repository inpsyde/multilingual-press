<?php
/**
 * class Mlp_Global_Switcher
 *
 * @version 2014.09.09
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Global_Switcher {

	/**
	 * @type string
	 */
	const TYPE_GET = 'get';

	/**
	 * @type string
	 */
	const TYPE_POST = 'post';

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var array
	 */
	private $storage = array();

	/**
	 * Constructor.
	 *
	 * @param string $type Either 'get' or 'post'. Use the constants to avoid typos.
	 */
	public function __construct( $type ) {

		if ( self::TYPE_GET === $type )
			$this->type = $type;

		$this->type = self::TYPE_POST;
	}

	/**
	 * @return int Number of removed elements.
	 */
	public function strip() {

		if ( self::TYPE_GET === $this->type )
			return $this->strip_get();

		return $this->strip_post();
	}

	/**
	 * @return int Number of filled elements.
	 */
	public function fill() {

		if ( empty ( $this->storage ) )
			return 0;

		$amount = count( $this->storage );

		if ( self::TYPE_GET === $this->type )
			$this->fill_get();
		else
			$this->fill_post();

		return $amount;
	}

	/**
	 * @return int Number of removed elements.
	 */
	private function strip_get() {

		if ( empty ( $_GET ) )
			return 0;

		$amount = count( $_GET );

		foreach ( $_GET as $name => $value ) {
			$this->storage[ $name ] = $value;
			unset ( $_REQUEST[ $name ], $_GET[ $name ] );
		}

		return $amount;
	}

	/**
	 * @return void
	 */
	private function fill_get() {

		foreach ( $this->storage as $name => $value )
			$_REQUEST[ $name ] = $_GET[ $name ] = $value;
	}

	/**
	 * @return int Number of removed elements.
	 */
	private function strip_post() {

		if ( empty ( $_POST ) )
			return 0;

		$amount = count( $_POST );

		foreach ( $_POST as $name => $value ) {
			$this->storage[ $name ] = $value;
			unset ( $_REQUEST[ $name ], $_POST[ $name ] );
		}

		return $amount;
	}

	/**
	 * @return void
	 */
	private function fill_post() {

		foreach ( $this->storage as $name => $value )
			$_REQUEST[ $name ] = $_POST[ $name ] = $value;
	}
}