<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\Common\Type\AliasAwareLanguage;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

class LanguageListTable extends \WP_List_Table
{
	protected $screen = 'mlp_language_manager';

	/**
	 * Used in inherited method display()
	 *
	 * @var array
	 */
	protected $_args = [];

	/**
	 * List of languages.
	 *
	 * We use "items" here for compatibility with the extended class.
	 *
	 * @var array
	 */
	public $items = [];

	/**
	 * @var array column headers
	 */
	public $_column_headers;

	/**
	 * @var array columns
	 */
	public $columns;

	/**
	 * @var array column names
	 */
	public $column_names;

	/**
	 * LanguageListTable constructor.
	 *
	 * @param array $languages
	 */
	public function __construct( array $languages )
	{
		$this->_args = [
			'plural'   => '',
			'singular' => '',
			'ajax'     => false,
			'screen'   => null,
		];
		$this->items = $languages;

		$this->columns = [
			LanguagesTable::COLUMN_NATIVE_NAME    => __( 'Native name', 'multilingualpress' ),
			LanguagesTable::COLUMN_ENGLISH_NAME   => __( 'English name', 'multilingualpress' ),
			LanguagesTable::COLUMN_RTL            => __( 'RTL', 'multilingualpress' ),
			LanguagesTable::COLUMN_HTTP_CODE      => __( 'HTTP', 'multilingualpress' ),
			LanguagesTable::COLUMN_ISO_639_1_CODE => __( 'ISO&#160;639-1', 'multilingualpress' ),
			LanguagesTable::COLUMN_LOCALE         => __( 'Locale', 'multilingualpress' ),
			LanguagesTable::COLUMN_PRIORITY       => __( 'Priority', 'multilingualpress' ),
		];

		$this->column_names = array_keys( $this->columns );

		parent::__construct();
	}

	/**
	 * Used as a callback to show this table.
	 *
	 * @return void
	 */
	public function setup()
	{
		$this->prepare_items();
		$this->display();
	}

	public function prepare_items()
	{
		$this->_column_headers = [
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns(),
		];
	}

	public function get_hidden_columns() : array
	{
		return [ 'id' ];
	}

	public function display_rows_or_placeholder()
	{
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			echo 'nothing found';
		}
	}

	/**
	 *
	 * @param object $item
	 * @param string $column_name
	 * @return string
	 */
	protected function column_default( $item, $column_name ) : string
	{
		if ( LanguagesTable::COLUMN_NATIVE_NAME === $column_name
		     || LanguagesTable::COLUMN_ENGLISH_NAME === $column_name ) {

			$url = add_query_arg( LanguagesTable::COLUMN_ID, $item[ LanguagesTable::COLUMN_ID ] );
			$url = esc_url( $url );

			return sprintf( '<a href="%1$s">%2$s</a>', $url, $item[ $column_name ] );
		}

		return (string) $item[ $column_name ];
	}

	/**
	 * Defines the columns to use in your listing table
	 *
	 * @see prepare_items()
	 *
	 * @return array
	 */
	public function get_columns() : array
	{
		return $this->columns;
	}
}
