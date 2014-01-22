<?php # -*- coding: utf-8 -*-
class Mlp_Language_Table_Data {

	private $db, $formatter;
	/**
	 * Constructor.
	 */
	public function __construct( Mlp_Db_Schema_Interface $db, $formatter = '' ) {
		$this->db        = $db;
		$this->formatter = $formatter;
	}

	public function get_languages() {

		$results = $this->query_languages();

		if ( empty ( $results ) )
			return array();

		if ( ! empty ( $this->formatter ) )
			array_walk( $results, array( $this, 'walk_db_languages' ) );

		return $results;
	}

	public function save_languages() {

		$msg = '';

		if ( ! wp_verify_nonce( $_POST[ $this->get_nonce_name() ], $this->get_nonce_action() ) )
			die( 'invalid request' );

		if ( isset ( $_POST['delete'] ) && ! empty ( $_POST['delete_languages'] ) ) {
			$num = $this->delete_languages( $_POST['delete_languages'] );
			$msg = "deleted-$num";
		}
		elseif ( isset ( $_POST['languages'][0] ) && is_array( $_POST['languages'][0] ) ) {
			$this->insert_language( $_POST['languages'][0] );
			unset ( $_POST['languages'][0] );
			$msg = "added";
		}
		else {
			$languages = $this->prepare_post_languages( $_POST['languages'] );
			$existing  = $this->query_languages();
			$to_update = $this->get_update_ids( $languages, $existing );

			if ( ! empty ( $to_update ) ) {
				$this->update_languages( $to_update, $languages );
				$msg = "updated-" . count( $to_update );
			}
		}

		$url = remove_query_arg( 'msg', $_POST['_wp_http_referer'] );

		if ( '' !== $msg )
			$url = add_query_arg( 'msg', $msg, $url );

		wp_safe_redirect( $url );
		/**/
		exit;
	}

	public function delete_languages( $ids ) {

		global $wpdb;

		$ids   = array_map( 'absint', $ids );
		$ids   = join( ',', $ids );
		$table = $this->db->get_table_name();
		$query = "DELETE FROM $table WHERE `ID` IN ( $ids )";
		return $wpdb->query( $query );
	}

	public function insert_language( Array $data ) {

		global $wpdb;

		if ( '' === $data['native_name'] . $data['english_name'] . $data['custom_name'] )
			return;

		if ( empty ( $data['iso_639_1'] ) )
			return;

		$wpdb->insert( $this->db->get_table_name(), $data );
	}

	public function get_dummy_data() {
		return new $this->formatter( new stdClass );
	}

	public function get_nonce_name() {
		return 'mlp_language_table_nonce';
	}

	public function get_nonce_action() {
		return 'mlp_update_languages';
	}

	private function prepare_post_languages( $languages ) {

		$out = array();

		foreach ( $languages as $id => $language ) {

			if ( ! isset ( $language['is_rtl'] ) or '1' !== $language['is_rtl'] )
				$language['is_rtl'] = '0';

			if ( empty ( $language['http_name'] ) ) {
				if ( ! empty ( $language['iso_639_1'] ) )
					$language['http_name'] = str_replace( '_', '-', $language['iso_639_1'] );
				elseif ( ! empty ( $language['wp_locale'] ) )
					$language['http_name'] = str_replace( '_', '-', $language['wp_locale'] );
			}

			if ( empty ( $language['iso_639_1'] ) ) {
				if ( ! empty ( $language['http_name'] ) )
					$language['iso_639_1'] = str_replace( '-', '_', $language['http_name'] );
				elseif ( ! empty ( $language['wp_locale'] ) )
					$language['iso_639_1'] = $language['wp_locale'];
			}

			// This might fail. wp_locale format doesn't follow any fixed standard
			if ( empty ( $language['wp_locale'] ) ) {
				if ( ! empty ( $language['http_name'] ) )
					$language['wp_locale'] = str_replace( '-', '_', $language['http_name'] );
				elseif ( ! empty ( $language['iso_639_1'] ) )
					$language['wp_locale'] = $language['iso_639_1'];
			}

			if ( 10 < $language['priority'] )
				$language['priority'] = '10';

			if ( 1 > $language['priority'] )
				$language['priority'] = '1';

			$out[ $id ] = $language;
		}

		return $out;
	}

	private function update_languages( $ids, $languages ) {

		global $wpdb;

		$queries = array();

		foreach ( $ids as $id ) {
			$wpdb->update(
				$this->db->get_table_name(),
				$languages[ $id ],
				array( 'ID' => $id ),
				'%s', // field format
				'%d'  // WHERE format
			);
			$queries[ $id ] = $wpdb->func_call;
		}

		return $queries;
	}

	private function get_update_ids( $new, $old ) {

		$ids = array ();

		foreach ( $new as $id => $data ) {

			if ( ! isset ( $old[ $id ] ) ) {
				$ids[] = (int) $id;
				continue;
			}

			$old[ $id ] = (array) $old[ $id ];
			unset( $old[ $id ]['ID'] );

			if ( $this->has_changed( $data, $old[ $id ] ) )
				$ids[] = (int) $id;
		}

		return $ids;
	}

	private function has_changed( $new, $old ) {

		foreach ( $new as $key => $value ) {

			if ( 'is_rtl' === $key && '0' == $value && ! isset ( $old[ $key ] ) )
				continue;

			if ( ! isset ( $old[ $key ] ) or $value != $old[ $key ] )
				return TRUE;
		}

		return FALSE;
	}

	private function query_languages() {

		global $wpdb;

		$table = $this->db->get_table_name();
		$query = "SELECT * FROM $table ORDER BY `english_name`";


		$result = $wpdb->get_results( $query, OBJECT_K );
		//print '<pre>$wpdb->queries = ' . esc_html( var_export( end($wpdb->queries), TRUE ) ) . '</pre>';
		return $result;
	}

	/**
	 *
	 * @param  stdClass $lang
	 * @return Mlp_Language_Formatter
	 */
	private function walk_db_languages( &$lang ) {
		$lang = new $this->formatter( $lang );
	}
}