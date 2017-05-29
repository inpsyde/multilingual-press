<?php # -*- coding: utf-8 -*-

/**
 * Null translation implementation.
 */
class Mlp_Null_Translation implements Mlp_Translation_Interface {

	/**
	 * @var Mlp_Language_Interface
	 */
	private $language;

	/**
	 * @var Mlp_Url_Interface
	 */
	private $url;

	/**
	 * @return int
	 */
	public function get_source_site_id() {

		return 0;
	}

	/**
	 * @return string
	 */
	public function get_page_type() {

		return '';
	}

	/**
	 * @return Mlp_Url_Interface
	 */
	public function get_icon_url() {

		if ( ! $this->url ) {
			$this->url = new Mlp_Url( '' );
		}

		return $this->url;
	}

	/**
	 * @return int
	 */
	public function get_target_site_id() {

		return 0;
	}

	/**
	 * @return string
	 */
	public function get_target_title() {

		return '';
	}

	/**
	 * @return Mlp_Language_Interface
	 */
	public function get_language() {

		if ( ! $this->language ) {
			$this->language = new Mlp_Null_Language();
		}

		return $this->language;
	}

	/**
	 * @return int
	 */
	public function get_target_content_id() {

		return 0;
	}

	/**
	 * @return string
	 */
	public function get_remote_url() {

		return '';
	}
}
