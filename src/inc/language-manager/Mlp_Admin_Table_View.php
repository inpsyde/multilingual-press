<?php # -*- coding: utf-8 -*-

/**
 * Class Mlp_Admin_Table_View
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Admin_Table_View {

	/**
	 *
	 *
	 * @var Mlp_Data_Access
	 */
	private $data;

	/**
	 *
	 *
	 * @var Mlp_Html_Interface
	 */
	private $html;

	/**
	 *
	 *
	 * @var array
	 */
	private $columns;

	/**
	 *
	 *
	 * @var
	 */
	private $id;

	/**
	 *
	 *
	 * @var
	 */
	private $name;

	/**
	 * @param Mlp_Data_Access    $data
	 * @param Mlp_Html_Interface $html
	 * @param Mlp_Browsable      $pagination_data
	 * @param array              $columns
	 * @param int                $id
	 * @param string             $name
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

	/**
	 * @return void
	 */
	public function show_table() {

		?>
		<table id="<?php echo esc_attr( $this->id ); ?>" class="widefat">
			<?php $this->print_headers(); ?>
			<tbody>
				<?php $this->print_tbody(); ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * @return void
	 */
	private function print_tbody() {

		$params = array(
			'page' => $this->pagination_data->get_current_page()
		);
		$rows = $this->data->get_items( $params );

		if ( ! $rows ) {
			?>
			<tr>
				<td colspan="<?php echo count( $this->columns ); ?>">
					<p>
						<?php _e( 'No items found. We recommend to reinstall this plugin.', 'multilingual-press' ); ?>
					</p>
				</td>
			</tr>
			<?php

			return;
		}

		foreach ( $rows as $id => $row ) {
			$this->print_row( $id, $row );
		}
	}

	/**
	 * @param $id
	 * @param $row
	 * @return void
	 */
	private function print_row( $id, $row ) {

		?>
		<tr<?php echo $this->get_alternating_class(); ?>>
			<?php foreach ( $this->columns as $col => $data ) : ?>
				<td>
					<?php
					$content = empty( $row->$col ) ? '' : $row->$col;

					$attrs = empty( $data['attributes'] ) ? array() : $data['attributes'];

					if ( empty( $data['type'] ) ) {
						$data['type'] = 'text';
					}

					switch ( $data['type'] ) {
						case 'input_text':
							echo $this->get_text_input( $id, $col, $content, $attrs );
							break;

						case 'input_checkbox':
							echo $this->get_checkbox_input( $id, $col, $content, $attrs );
							break;

						case 'input_number':
							echo $this->get_number_input( $id, $col, $content, $attrs );
							break;

						default:
							echo $content;
					}
					?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php
	}

	/**
	 * @param       $id
	 * @param       $col
	 * @param       $value
	 * @param array $attributes
	 * @return string
	 */
	private function get_checkbox_input( $id, $col, $value, Array $attributes = array() ) {

		list( $name, $attrs ) = $this->prepare_input_data( $id, $col, $value, $attributes );

		return sprintf(
			'<input type="checkbox" name="%s" value="1"%s%s>',
			esc_attr( $name ),
			$attrs,
			checked( 1, $value, false )
		);
	}

	/**
	 * @param       $id
	 * @param       $col
	 * @param       $value
	 * @param array $attributes
	 * @return string
	 */
	private function get_number_input( $id, $col, $value, Array $attributes = array() ) {

		list( $name, $attrs, $value ) = $this->prepare_input_data( $id, $col, $value, $attributes );

		return sprintf(
			'<input type="number" name="%s" value="%d"%s>',
			esc_attr( $name ),
			$value,
			$attrs
		);
	}

	/**
	 * @param       $id
	 * @param       $col
	 * @param       $value
	 * @param array $attributes
	 * @return string
	 */
	private function get_text_input( $id, $col, $value, Array $attributes = array() ) {

		list( $name, $attrs, $value ) = $this->prepare_input_data( $id, $col, $value, $attributes );

		return sprintf(
			'<input type="text" name="%s" value="%s"%s>',
			esc_attr( $name ),
			esc_attr( $value ),
			$attrs
		);
	}

	/**
	 * @param $id
	 * @param $col
	 * @param $value
	 * @param $attributes
	 * @return array
	 */
	private function prepare_input_data( $id, $col, $value, $attributes ) {

		return array (
			$this->get_input_name( $id, $col ),
			$this->html->array_to_attrs( $attributes ),
			$value
		);
	}

	/**
	 * @param $id
	 * @param $col
	 * @return string
	 */
	private function get_input_name( $id, $col ) {

		return $this->name . '[' . $id . '][' . $col . ']';
	}

	/**
	 * @return void
	 */
	private  function print_headers() {

		printf(
			'<thead><tr>%1$s</tr></thead><tfoot><tr>%1$s</tr></tfoot>',
			$this->get_header()
		);
	}

	/**
	 * @return string
	 */
	private function get_header() {

		$row = '';

		foreach ( $this->columns as $params ) {
			$row .= '<th scope="col">';

			if ( ! empty( $params['header'] ) ) {
				$row .= esc_html( $params['header'] );
			}

			$row .= '</th>';
		}

		return $row;
	}

	/**
	 * @return string
	 */
	private function get_alternating_class() {

		static $count = 0;

		return 0 === $count++ % 2 ? ' class="alternate"' : '' ;
	}
}
