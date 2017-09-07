<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Array_Diff
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Array_Diff {

	/**
	 * @var array
	 */
	private $columns;

	/**
	 * Constructor.
	 *
	 * @param array $columns
	 */
	public function __construct( array $columns ) {
		$this->columns = $columns;
	}

	/**
	 * @param array $old
	 * @param array $new
	 * @return array
	 */
	public function get_difference( array $old, array $new ) {

		$diff = array();
		$new  = $this->normalize_new_array( $new, $old );

		foreach ( $old as $old_id => $old_data_array ) {

			if ( ! isset( $new[ $old_id ] ) ) {
				continue;
			}

			unset( $old_data_array['ID'] );

			unset( $old_data_array['custom_name'] );

			$arr_diff = array_diff_assoc( $old_data_array, $new[ $old_id ] );

			if ( ! empty( $arr_diff ) ) {
				$diff[ $old_id ] = $new[ $old_id ];
			}
		}

		return $diff;
	}

	/**
	 * @param array $new
	 * @param array $old
	 * @return array
	 */
	private function normalize_new_array( array $new, array $old ) {

		$out = array();

		foreach ( $new as $new_id => $new_data ) {

			$out[ $new_id ] = array();

			foreach ( $this->columns as $col_name => $col_params ) {

				if ( ! isset( $old[ $new_id ] ) ) {
					continue;
				}

				$out[ $new_id ][ $col_name ] = $this->normalize_new_field(
					$col_name,
					$col_params,
					$new_data,
					$old[ $new_id ]
				);
			}
		}

		return $out;
	}

	/**
	 * @param $col_name
	 * @param $col_params
	 * @param $new_data
	 * @param $old_data
	 * @return int|mixed|string
	 */
	private function normalize_new_field( $col_name, $col_params, $new_data, $old_data ) {

		$return = '';

		if ( 'input_text' === $col_params['type'] ) {
			if ( ! isset( $new_data[ $col_name ] ) ) {
				return '';
			}

			return (string) $new_data[ $col_name ];
		}

		if ( 'input_checkbox' === $col_params['type'] ) {
			if ( ! isset( $new_data[ $col_name ] ) || '0' === $new_data[ $col_name ] ) {
				return 0;
			}

			return 1;
		}

		if ( 'input_number' === $col_params['type'] ) {

			if ( ! isset( $new_data[ $col_name ] ) ) {
				if ( ! isset( $col_params['attributes'] ) ) {
					return isset( $old_data[ $col_name ] ) ? $old_data[ $col_name ] : 1;
				}

				if ( isset( $col_params['attributes']['min'] ) ) {
					return $col_params['attributes']['min'];
				}

				if ( isset( $col_params['attributes']['max'] ) ) {
					return $col_params['attributes']['max'];
				}

				// might be wrong. we might need a 'default' value for missing data.
				return isset( $old_data[ $col_name ] ) ? $old_data[ $col_name ] : 1;
			}

			// $new_data[ $col_name ] is set

			if ( ! isset( $col_params['attributes'] ) ) {
				return (int) $new_data[ $col_name ];
			}

			if ( ! isset( $col_params['attributes']['min'] )
				&& ! isset( $col_params['attributes']['max'] )
				) {
				return (int) $new_data[ $col_name ];
			}

			// at least one of 'min' or 'max' is given

			if ( isset( $col_params['attributes']['min'] ) ) {
				$return = max(
					array(
						(int) $new_data[ $col_name ],
						(int) $col_params['attributes']['min'],
					)
				);
			}

			if ( isset( $col_params['attributes']['max'] ) ) {
				$return = min(
					array(
						(int) $new_data[ $col_name ],
						(int) $col_params['attributes']['max'],
					)
				);
			}

			return $return;
		}

		return $return;
	}
}
