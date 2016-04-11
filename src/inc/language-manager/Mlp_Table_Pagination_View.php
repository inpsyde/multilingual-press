<?php # -*- coding: utf-8 -*-
/**
 * Create pagination output for tables.
 *
 * Outputs markup that will be styled by WordPress default stylesheets
 * in admin backend.
 *
 * @author  Inpsyde GmbH, MarketPress, toscho
 * @version 2013.12.22
 * @license GPL
 * @package MultilingualPress\Pagination\Views
 * @uses    Mlp_Browsable
 */
class Mlp_Table_Pagination_View {

	/**
	 * Source for actual pagination information.
	 *
	 * @type Mlp_Browsable
	 */
	private $data;

	/**
	 * Stores the result, so it doesn't have to be created twice.
	 *
	 * @type string
	 */
	private $pagination    = '';

	/**
	 * Complete, sanitized current request URL.
	 *
	 * @type string
	 */
	private $current_url;

	/**
	 * Current page number.
	 *
	 * @type integer
	 */
	private $current_page;

	/**
	 * Total number of all items.
	 *
	 * @type integer
	 */
	private $total_items;

	/**
	 * Total number of all pages.
	 *
	 * @type integer
	 */
	private $total_pages;

	/**
	 * CSS class for previous and first page links.
	 *
	 * @type string
	 */
	private $disable_first = '';

	/**
	 * CSS class for next and last page links.
	 *
	 * @type string
	 */
	private $disable_last  = '';

	/**
	 * Constructor.
	 *
	 * @param Mlp_Browsable $data
	 */
	public function __construct( Mlp_Browsable $data ) {
		$this->data = $data;
	}

	/**
	 * Print the markup.
	 *
	 * @return void
	 */
	public function print_pagination() {

		// Do not work twice.
		if ( '' !== $this->pagination ) {
			echo $this->pagination;

			return;
		}

		$this->set_context_vars();

		echo $this->pagination;

		$page_class = 1 === $this->total_pages ? ' one-page' : '';
		?>
		<div class="tablenav-pages<?php echo esc_attr( $page_class ); ?>">
			<?php echo $this->get_item_count() ?>
			<?php if ( 1 < $this->total_pages ) { ?>
				<?php echo $this->get_pagination_links(); ?>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Fill class members to be used by other methods.
	 *
	 * @return void
	 */
	private function set_context_vars() {

		$this->total_pages  = $this->data->get_total_pages();
		$this->total_items  = $this->data->get_total_items();
		$this->current_page = $this->data->get_current_page();
		$this->current_url  = $this->get_current_url();

		if ( $this->current_page === 1 ) {
			$this->disable_first = ' disabled';
		}

		if ( $this->current_page === $this->total_pages ) {
			$this->disable_last = ' disabled';
		}
	}

	/**
	 * Get the link markup.
	 *
	 * @return string
	 */
	private function get_pagination_links() {

		return "\n<span class='pagination-links'>"
			. $this->get_first_page_link()      . ' '
			. $this->get_previous_page_link()   . ' '
			. $this->get_current_page_display() . ' '
			. $this->get_next_page_link()       . ' '
			. $this->get_last_page_link()
			. '</span>';
	}

	/**
	 * Get text for "page number of total pages".
	 *
	 * @return string
	 */
	private function get_current_page_display() {

		$total = sprintf(
			"<span class='total-pages'>%s</span>",
			number_format_i18n( $this->total_pages )
		);

		$paging = sprintf(
			_x( '%1$s of %2$s', 'paging', 'multilingual-press' ),
			number_format_i18n( $this->current_page ),
			$total
		);

		return '<span class="paging-input">' . $paging . '</span>';
	}

	/**
	 * Get markup for last page link.
	 *
	 * @return string
	 */
	private function get_last_page_link() {

		return $this->get_anchor(
			$this->get_paged_url( $this->total_pages ),
			__( 'Go to the last page', 'multilingual-press' ),
			'last-page' . $this->disable_last,
			'&raquo;'
		);
	}

	/**
	 * Get markup for next page link.
	 *
	 * @return string
	 */
	private function get_next_page_link() {

		$page = min( $this->total_pages, $this->current_page + 1 );

		return $this->get_anchor(
			$this->get_paged_url( $page ),
			__( 'Go to the next page', 'multilingual-press' ),
			'next-page' . $this->disable_last,
			'&rsaquo;'
		);
	}

	/**
	 * Get markup for previous page link.
	 *
	 * @return string
	 */
	private function get_previous_page_link() {

		return $this->get_anchor(
			$this->get_paged_url( max( 1, $this->current_page -1 ) ),
			__( 'Go to the previous page', 'multilingual-press' ),
			'prev-page' . $this->disable_first,
			'&lsaquo;'
		);
	}

	/**
	 * Get markup for last page link.
	 *
	 * @return string
	 */
	private function get_first_page_link() {

		return $this->get_anchor(
			$this->get_paged_url( 1 ),
			__( 'Go to the first page', 'multilingual-press' ),
			'first-page' . $this->disable_first,
			'&laquo;'
		);
	}

	/**
	 * Get anchor markup.
	 *
	 * @param  string $url   href attribute
	 * @param  string $title Title attribute.
	 * @param  string $class CSS class.
	 * @param  string $text  Visible anchor text.
	 * @return string
	 */
	private function get_anchor( $url, $title, $class, $text ) {

		return sprintf(
			'<a href="%2$s" class="%3$s" title="%4$s">%1$s</a>',
			esc_html( $text ),
			esc_url( $url ),
			esc_attr( $class ),
			esc_attr( $title )
		);
	}

	/**
	 * Get markup for all item display.
	 *
	 * @return string
	 */
	private function get_item_count() {

		$num = sprintf(
			_n( '1 item', '%s items', (int) $this->total_items, 'multilingual-press' ),
			number_format_i18n( $this->total_items )
		);

		return '<span class="displaying-num">' . esc_html( $num ) . '</span>';
	}

	/**
	 * Get escaped URL for a specific page number.
	 *
	 * @param  integer $page
	 * @return string
	 */
	private function get_paged_url( $page ) {

		if ( 1 === $page ) {
			return remove_query_arg( 'paged', $this->current_url );
		}

		return add_query_arg( 'paged', $page, $this->current_url );
	}

	/**
	 * Get current request URL.
	 *
	 * @return string
	 */
	private function get_current_url() {

		$url = set_url_scheme(
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
		);

		// We use 'msg' as parameter internally. Not needed for pagination.
		return remove_query_arg( 'msg', $url );
	}
}
