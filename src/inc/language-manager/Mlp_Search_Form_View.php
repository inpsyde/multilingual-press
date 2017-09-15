<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Search_Form_View
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Search_Form_View {

	/**
	 * @param array $attributes
	 * @return string
	 */
	public function get_search_field( array $attributes = array() ) {

		$default = array(
			'type'  => 'search',
			'name'  => 's',
			'value' => '',
		);

		$attributes = array_merge( $default, $attributes );

		return '<input ' . $this->html_attributes( $attributes ) . ' />';
	}

	/**
	 * @param $text
	 * @return string
	 */
	public function get_search_button( $text ) {

		return get_submit_button( $text, 'secondary', 'submit', false );
	}

	/**
	 * @param  array $attributes
	 * @return string
	 */
	private function html_attributes( array $attributes ) {

		$return = '';

		foreach ( $attributes as $key => $value ) {
			$return .= " $key='" . esc_attr( $value ) . "'";
		}

		return $return;
	}
}
