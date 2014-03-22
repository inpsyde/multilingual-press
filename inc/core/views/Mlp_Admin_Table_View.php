<?php # -*- coding: utf-8 -*-

class Mlp_Admin_Table_View {

	private $data;
	private $html;
	private $columns;
	private $id;
	private $name;

	/**
	 * Constructor.
	 */
	public function __construct(
		Mlp_Data_Access    $data,
		Mlp_Html_Interface $html,
		Mlp_Browsable      $pagination_data,
		Array              $columns,
		$id,
		$name
	) {

		$this->data            = $data;
		$this->html            = $html;
		$this->pagination_data = $pagination_data;
		$this->columns         = $columns;
		$this->id              = $id;
		$this->name            = $name;
	}

	public function show_table() {
		?>
		<table id="<?php print $this->id; ?>" class="widefat">
			<?php
			$this->print_headers();
			?>
			<tbody>
			<?php
			$this->print_tbody();
			?>
			</tbody>
		</table>
		<?php
	}

	private function print_tbody() {

		$rows = $this->data->get_items( $this->pagination_data->get_current_page() );

		if ( empty ( $rows ) )
			return print '<tr><td colspan="' . count( $this->columns ) . '"><p>'
				. __( 'No items found. We recommend to reinstall this plugin.', 'multilingualpress' )
				. '</p></td></tr>';

		foreach ( $rows as $id => $row )
			$this->print_row( $id, $row );
	}

	private function print_row( $id, $row ) {

		print '<tr' . $this->get_alternating_class() . '>';

		foreach ( $this->columns as $col => $data ) {

			$content = empty ( $row->$col ) ? '' : $row->$col;
			$attrs   = empty ( $data[ 'attributes' ] ) ? array() : $data[ 'attributes' ];

			if ( empty ( $data[ 'type' ] ) )
				$data[ 'type' ] = 'text';

			switch ( $data[ 'type' ] ) {
				case 'input_text':
					$content = $this->get_text_input( $id, $col, $content, $attrs );
					break;
				case 'input_checkbox':
					$content = $this->get_checkbox_input( $id, $col, $content, $attrs );
					break;
				case 'input_number':
					$content = $this->get_number_input( $id, $col, $content, $attrs );
					break;
				case 'text':
				default:
			}

			print "<td>$content</td>";
		}

		print '</tr>';
	}

	private function get_checkbox_input( $id, $col, $value, Array $attributes = array() ) {

		list ( $name, $attrs ) = $this->prepare_input_data( $id, $col, $value, $attributes );
		$checked = checked( 1, $value, FALSE );

		return "<input type='checkbox' name='$name' value='1' $attrs $checked>";
	}

	private function get_number_input( $id, $col, $value, Array $attributes = array() ) {

		list ( $name, $attrs, $value ) = $this->prepare_input_data( $id, $col, $value, $attributes );

		return "<input type='number' name='$name' value='$value' $attrs>";
	}

	private function get_text_input( $id, $col, $value, Array $attributes = array() ) {

		list ( $name, $attrs, $value ) = $this->prepare_input_data( $id, $col, $value, $attributes );

		return "<input type='text' name='$name' value='$value' $attrs>";
	}

	private function prepare_input_data( $id, $col, $value, $attributes ) {
		return array (
			$this->get_input_name( $id, $col ),
			$this->html->array_to_attrs( $attributes ),
			esc_attr( $value )
		);
	}

	private function get_input_name( $id, $col ) {
		return $this->name . '[' . $id . '][' . $col . ']';
	}

	private  function print_headers() {
		printf(
			'<thead><tr>%1$s</tr></thead><tfoot><tr>%1$s</tr></tfoot>',
			$this->get_header()
		);
	}

	private function get_header() {

		$row = '';

		foreach ( $this->columns as $id => $params ) {

			$row .= '<th scope="col">';

			if ( ! empty ( $params[ 'header' ] ) )
				$row .= $params[ 'header' ];

			$row .= '</th>';
		}

		return $row;
	}

	private function get_alternating_class() {

		static $count = 0;

		return 0 === $count++ % 2 ? ' class="alternate"' : '' ;
	}
}