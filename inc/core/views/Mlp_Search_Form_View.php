<?php # -*- coding: utf-8 -*-
class Mlp_Search_Form_View {

	public function get_search_field( Array $attributes = array() ) {

		$default = array (
			'type'  => 'search',
			'name'  => 's',
			'value' => ''
		);
		$attrs   = array_merge( $default, $attributes );

		return '<input ' . $this->html_attributes( $attrs ) . ' />';
	}

	public function get_search_button( $text ) {

		return get_submit_button( $text, 'secondary', 'submit', FALSE );
	}

	private function html_attributes( Array $attrs ) {

		$return = '';

		foreach ( $attrs as $key => $value )
			$return .= " $key='" . esc_attr( $value ) . "'";

		return $return;
	}
}