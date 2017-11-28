<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\Common\Labels;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

class LanguageListTable extends \WP_List_Table
{
	/**
	 * @var string
	 */
	protected $screen;

	/**
	 * Used in inherited method display()
	 *
	 * @var array
	 */
	protected $_args = [
		'plural'   => '',
		'singular' => '',
		'ajax'     => false,
		'screen'   => null,
	];

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
	 * LanguageListTable constructor.
	 *
	 * @param array  $languages
	 * @param Labels $labels
	 */
	public function __construct( array $languages, Labels $labels )
	{
		$this->items   = $languages;
		$this->columns = $labels->all();
		$this->_args['screen'] = LanguageManagerSettingsPageView::CURRENT_SCREEN;

		parent::__construct([ 'screen' => LanguageManagerSettingsPageView::CURRENT_SCREEN ]);
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
			return;
		}
		$this->no_items();
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

			$url = add_query_arg( LanguageManagerSettingsPageView::QUERY_ARG_ID, $item[ LanguagesTable::COLUMN_ID ] );

			return sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), $item[ $column_name ] );
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
