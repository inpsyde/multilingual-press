<?php
/**
 * Load files from a given directory.
 *
 * @version 2014.06.30
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Inpsyde_Directory_Load implements Inpsyde_Autoload_Rule_Interface {

	/**
	 * Directory to search in.
	 *
	 * @var string
	 */
	private $dir ='';

	/**
	 * List of found classes.
	 *
	 * @var array
	 */
	private $found = array ();

	/**
	 * Constructor.
	 *
	 * @param string $dir
	 */
	public function __construct( $dir ) {
		$this->dir = rtrim( $dir, '/\\' );
	}

	/**
	 * Parse class/trait/interface name and load file.
	 *
	 * @param  string $name
	 * @return boolean
	 */
	public function load( $name ) {

		if ( empty ( $this->found ) )
			$this->found = $this->read_files();

		if ( ! isset ( $this->found[ $name ] ) )
			return FALSE;

		require $this->dir . "/$name.php";

		return TRUE;
	}

	/**
	 * Read the existing files once.
	 *
	 * @return array
	 */
	private function read_files() {

		$return = array();
		$files  = glob( $this->dir . '/*.php' );

		// Catch empty values to prevent multiple attempts to read the directory.
		if ( FALSE === $files )
			return array ( 'error' );

		if ( array () === $files )
			return array ( 'empty' );

		foreach ( $files as $file )
			$return[ basename( $file, '.php' ) ] = 1;

		return $return;
	}
}