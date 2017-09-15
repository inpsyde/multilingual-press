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
		Mlp_Data_Access $data,
		Mlp_Html_Interface $html,
		Mlp_Browsable $pagination_data,
		array $columns,
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
			'page' => $this->pagination_data->get_current_page(),
		);
		$rows = $this->data->get_items( $params );

		if ( ! $rows ) {
			?>
			<tr>
				<td colspan="<?php echo count( $this->columns ); ?>">
					<p>
						<?php
						esc_html_e( 'No items found. We recommend to reinstall this plugin.', 'multilingual-press' );
						?>
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

		static $alternate = true;

		$alternate = ! $alternate;

		$class = $alternate ? 'alternate' : '';
		?>
		<tr class="<?php echo esc_attr( $class ); ?>">
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
							$this->print_text_input( $id, $col, $content, $attrs );
							break;

						case 'input_checkbox':
							$this->print_checkbox_input( $id, $col, $content, $attrs );
							break;

						case 'input_number':
							$this->print_number_input( $id, $col, $content, $attrs );
							break;

						default:
							echo wp_kses_post( $content );
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
	 * @return void
	 */
	private function print_checkbox_input( $id, $col, $value, array $attributes = array() ) {

		list( $name, $attrs ) = $this->prepare_input_data( $id, $col, $value, $attributes );
		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php echo esc_attr( $attrs ); ?>
			<?php checked( 1, $value ); ?>>
		<?php
	}

	/**
	 * @param       $id
	 * @param       $col
	 * @param       $value
	 * @param array $attributes
	 * @return void
	 */
	private function print_number_input( $id, $col, $value, array $attributes = array() ) {

		list( $name, $attrs, $value ) = $this->prepare_input_data( $id, $col, $value, $attributes );
		?>
		<input type="number" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"
			<?php echo esc_attr( $attrs ); ?>>
		<?php
	}

	/**
	 * @param       $id
	 * @param       $col
	 * @param       $value
	 * @param array $attributes
	 * @return void
	 */
	private function print_text_input( $id, $col, $value, array $attributes = array() ) {

		list( $name, $attrs, $value ) = $this->prepare_input_data( $id, $col, $value, $attributes );
		?>
		<input type="text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"
			<?php echo esc_attr( $attrs ); ?>>
		<?php
	}

	/**
	 * @param $id
	 * @param $col
	 * @param $value
	 * @param $attributes
	 * @return array
	 */
	private function prepare_input_data( $id, $col, $value, $attributes ) {

		return array(
			$this->get_input_name( $id, $col ),
			$this->html->array_to_attrs( $attributes ),
			$value,
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

		?>
		<thead>
		<tr><?php $this->print_header(); ?></tr>
		</thead>
		<tfoot>
		<tr><?php $this->print_header(); ?></tr>
		</tfoot>
		<?php
	}

	/**
	 * @return void
	 */
	private function print_header() {

		foreach ( $this->columns as $params ) {
			?>
			<th scope="col">
				<?php
				if ( ! empty( $params['header'] ) ) {
					echo esc_html( $params['header'] );
				}
				?>
			</th>
			<?php
		}
	}
}
