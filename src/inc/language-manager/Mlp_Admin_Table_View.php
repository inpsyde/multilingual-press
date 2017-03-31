<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\Common\Type\Language;

use function Inpsyde\MultilingualPress\attributes_array_to_string;

/**
 * Class Mlp_Admin_Table_View
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Admin_Table_View {

	/**
	 * @var Languages
	 */
	private $languages;

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
	 * @param Languages    $languages
	 * @param Mlp_Browsable      $pagination_data
	 * @param array              $columns
	 * @param int                $id
	 * @param string             $name
	 */
	public function __construct(
		Languages    $languages,
		Mlp_Browsable      $pagination_data,
		array              $columns,
		$id,
		$name
	) {

		$this->languages       = $languages;
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

		$rows = $this->languages->get_languages( [
			'number' => $this->pagination_data->get_items_per_page(),
			'page'   => $this->pagination_data->get_current_page(),
		] );
		if ( ! $rows ) {
			?>
			<tr>
				<td colspan="<?php echo count( $this->columns ); ?>">
					<p>
						<?php _e( 'No items found. We recommend to reinstall this plugin.', 'multilingualpress' ); ?>
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
	 * @param Language $row
	 * @return void
	 */
	private function print_row( $id, Language $row ) {

		?>
		<tr<?php echo $this->get_alternating_class(); ?>>
			<?php foreach ( $this->columns as $col => $data ) : ?>
				<td>
					<?php
					$content = $row[ $col ] ?? '';

					$attrs = empty( $data['attributes'] ) ? [] : $data['attributes'];

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
	private function get_checkbox_input( $id, $col, $value, array $attributes = [] ) {

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
	private function get_number_input( $id, $col, $value, array $attributes = [] ) {

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
	private function get_text_input( $id, $col, $value, array $attributes = [] ) {

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

		return [
			$this->get_input_name( $id, $col ),
			attributes_array_to_string( $attributes ),
			$value,
		];
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
