<?php

use Inpsyde\MultilingualPress\Common\Type\Translation;

/**
 * Various global helper methods
 *
 * Please use the functions in /inc/functions.php, do not access the methods of this class directly.
 *
 * @version 2015.06.26
 * @author  Inpsyde GmbH
 * @license GPL
 */
class Mlp_Helpers {

	/**
	 * @see Mlp_Helpers::insert_dependency()
	 * @type array
	 */
	private static $dependencies = [];

	/**
	 * Get the element ID in other blogs for the selected element
	 * with additional information.
	 *
	 * @param  int $element_id
	 * @param  string $type Either 'post' or 'term'
	 * @return array $elements
	 */
	public static function get_interlinked_permalinks( $element_id = 0, $type = '' ) {

		if ( ! is_singular() && ! is_tag() && !is_category() && ! is_tax() )
			return [];

		$return     = [];
		              /** @var Mlp_Language_Api $api */
		$api        = self::$dependencies[ 'language_api' ];
		$site_id    = get_current_blog_id();
		$element_id = \Inpsyde\MultilingualPress\get_default_content_id( $element_id );

		$args = [
			'site_id'    => $site_id,
			'content_id' => $element_id
		 ];
		if ( '' !== $type )
			$args['type'] = $type;

		// Array of Mlp_Translation instances, site IDs are the keys
		$related = $api->get_translations( $args );

		if ( empty ( $related ) )
			return $return;

		/** @var Translation $translation */
		foreach ( $related as $remote_site_id => $translation ) {

			if ( $site_id === (int) $remote_site_id )
				continue;

			$url = $translation->remote_url();

			if ( empty ( $url ) )
				continue;

			$language = $translation->language();

			$return[ $remote_site_id ] = [
				'post_id'        => $translation->target_content_id(),
				'post_title'     => $translation->remote_title(),
				'permalink'      => $url,
				'flag'           => $translation->icon_url(),
				/* 'lang' is the old entry, language_short the first part
				 * until the '_', long the complete language tag.
				 */
				'lang'           => $language->name( 'lang' ),
				'language_short' => $language->name( 'lang' ),
				'language_long'  => $language->name( 'language_long' ),
			];
		}

		return $return;
	}

	/**
	 * Get the linked elements and display them as a list.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function show_linked_elements( $args ) {

		$defaults = [
			'link_text'         => 'native',
			'display_flag'      => FALSE,
			'sort'              => 'priority',
			'show_current_blog' => FALSE,
			'strict'            => FALSE, // get exact translations only
		 ];
		$params = wp_parse_args( $args, $defaults );

		/**
		 * Get the Language API object.
		 *
		 * @param Mlp_Language_Api_Interface $language_api Language API object.
		 */
		$api = apply_filters( 'mlp_language_api', NULL );
		/** @var Mlp_Language_Api_Interface $api */
		if ( ! is_a( $api, 'Mlp_Language_Api_Interface' ) ) {
			return '';
		}

		$translations_args = [
			'strict'       => $params[ 'strict' ],
			'include_base' => $params[ 'show_current_blog' ],
		 ];
		$translations = $api->get_translations( $translations_args );
		if ( empty( $translations ) ) {
			return '';
		}

		$items = [];

		/** @var Translation $translation */
		foreach ( $translations as $site_id => $translation ) {
			$url = $translation->remote_url();
			if ( empty( $url ) ) {
				continue;
			}

			$language = $translation->language();

			$items[ $site_id ] = [
				'url'      => $url,
				'http'     => $language->name( 'http' ),
				'name'     => $language->name( $params[ 'link_text' ] ),
				'priority' => $language->priority(),
				'icon'     => (string) $translation->icon_url(),
			];
		}

		switch ( $params[ 'sort' ] ) {
			case 'blogid':
				ksort( $items );
				break;

			case 'priority':
				uasort( $items, function ( array $a, array $b ) {

					if ( $a['priority'] === $b['priority'] ) {
						return 0;
					}

					return ( $a['priority'] < $b['priority'] ) ? 1 : - 1;
				} );
				break;

			case 'name':
				uasort( $items, function ( array $a, array $b ) {

					return strcasecmp( $a['name'], $b['name'] );
				} );
				break;
		}

		$output = '<div class="mlp-language-box mlp_language_box"><ul>';

		foreach ( $items as $site_id => $item ) {
			$text = $item[ 'name' ];

			$img = ( ! empty( $item['icon'] ) && $params['display_flag'] )
				? '<img src="' . esc_url( $item['icon'] ) . '" alt="' . esc_attr( $item['name'] ) . '"> '
				: '';

			if ( get_current_blog_id() === $site_id ) {
				$output .= '<li><a class="current-language-item" href="">' . $img . esc_html( $text ) . '</a></li>';
			} else {
				$output .= sprintf(
					'<li><a rel="alternate" hreflang="%1$s" href="%2$s">%3$s%4$s</a></li>',
					esc_attr( $item['http'] ),
					esc_url( $item[ 'url' ] ),
					$img,
					esc_html( $text )
				);
			}
		}

		$output .= '</ul></div>';

		return $output;
	}

	/**
	 * @param  string $name
	 * @param  object $instance
	 * @return void
	 */
	public static function insert_dependency( $name, $instance ) {

		self::$dependencies[ $name ] = $instance;
	}
}
