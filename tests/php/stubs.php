<?php # -*- coding: utf-8 -*-

if ( ! class_exists( WP_Widget::class ) ) {
	class WP_Widget {

	}
}

if ( ! class_exists( Requests::class ) ) {

	class Requests {

		const HEAD = 'HEAD';

		public static $static_calls = [];

		public static function __callStatic( $name, array $args = [] ) {

			empty( self::$static_calls[ $name ] ) and self::$static_calls[ $name ] = [];
			self::$static_calls[ $name ][] = $args;
		}
	}
}

