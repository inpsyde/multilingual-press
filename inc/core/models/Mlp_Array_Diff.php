<?php # -*- coding: utf-8 -*-
class Mlp_Array_Diff {

	private $columns;
	/**
	 * Constructor.
	 */
	public function __construct( Array $columns ) {
		$this->columns = $columns;
	}

	public function get_difference( Array $old, Array $new ) {

		$diff = array ();
		$new  = $this->normalize_new_array( $new, $old );

		foreach ( $old as $old_id => $old_data_array ) {

			if ( ! isset ( $new[ $old_id ] ) )
				continue;

			unset ( $old_data_array[ 'ID' ] );
			unset ( $old_data_array[ 'custom_name' ] );

			$arr_diff = array_diff_assoc( $old_data_array, $new[ $old_id ] );

			//print '<pre>$arr_diff = ' . esc_html( var_export( $arr_diff, TRUE ) ) . '</pre>';

			if ( ! empty ( $arr_diff ) )
				 $diff[ $old_id ] = $new[ $old_id ];
		}

		return $diff;
	}

	private function normalize_new_array( Array $new, Array $old ) {

		$out = array();

		foreach ( $new as $new_id => $new_data ) {

			$out[ $new_id ] = array();

			foreach ( $this->columns as $col_name => $col_params ) {

				if ( ! isset ( $old[ $new_id ] ) )
					continue;

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

	private function normalize_new_field( $col_name, $col_params, $new_data, $old_data ) {

		if ( 'input_text' === $col_params[ 'type' ] ) {
			if ( ! isset ( $new_data[ $col_name ] ) )
				return '';

			return (string) $new_data[ $col_name ];
		}
		if ( 'input_checkbox' === $col_params[ 'type' ] ) {
			if ( ! isset ( $new_data[ $col_name ] ) or '0' === $new_data[ $col_name ] )
				return 0;

			return 1;
		}
		if ( 'input_number' === $col_params[ 'type' ] ) {

			if ( ! isset ( $new_data[ $col_name ] ) ) {
				if ( ! isset ( $col_params[ 'attributes' ] ) )
					return isset ( $old_data[ $col_name ] ) ? $old_data[ $col_name ] : 1;

				if ( isset ( $col_params[ 'attributes' ][ 'min' ] ) )
					return $col_params[ 'attributes' ][ 'min' ];

				if ( isset ( $col_params[ 'attributes' ][ 'max' ] ) )
					return $col_params[ 'attributes' ][ 'max' ];

				// might be wrong. we might need a 'default' value for missing data.
				return isset ( $old_data[ $col_name ] ) ? $old_data[ $col_name ] : 1;
			}

			// $new_data[ $col_name ] is set

			if ( ! isset ( $col_params[ 'attributes' ] ) )
				return (int) $new_data[ $col_name ];

			if ( ! isset ( $col_params[ 'attributes' ][ 'min' ] )
				&& ! isset ( $col_params[ 'attributes' ][ 'max' ] )
				)
				return (int) $new_data[ $col_name ];

			// at least one of 'min' or 'max' is given

			if ( isset ( $col_params[ 'attributes' ][ 'min' ] ) ) {
				$return = max(
					array (
						(int) $new_data[ $col_name ],
						(int) $col_params[ 'attributes' ][ 'min' ]
					)
				);
			}

			if ( isset ( $col_params[ 'attributes' ][ 'max' ] ) ) {
				$return = min(
					array (
						(int) $new_data[ $col_name ],
						(int) $col_params[ 'attributes' ][ 'max' ]
					)
				);
			}

			return $return;
		}
	}
}

/* $columns format:
array (
	'native_name' => array (
		'header'     => esc_html__( 'Native name', 'multilingualpress' ),
		'type'       => 'input_text',
		'attributes' => array (
			'size' => 20
		)
	),
	'is_rtl' => array (
		'header'     => esc_html__( 'RTL', 'multilingualpress' ),
		'type'       => 'input_checkbox',
		'attributes' => array (
			'size' => 20
		)
	),
	'priority' => array (
		'header'     => esc_html__( 'Priority', 'multilingualpress' ),
		'type'       => 'input_number',
		'attributes' => array (
			'min'  => 1,
			'max'  => 10,
			'size' => 3
		)
	),
)
 */