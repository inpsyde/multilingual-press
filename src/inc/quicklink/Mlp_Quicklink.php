<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\Translations as TranslationsAPI;
use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Request;
use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Common\Type\Translation;

/**
 * Displays an element link flyout tab in the frontend.
 */
class Mlp_Quicklink {

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var TranslationsAPI
	 */
	private $translations_api;

	/**
	 * @var Translation[]
	 */
	private $translations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param TranslationsAPI $translations_api   Translations API object.
	 * @param AssetManager               $asset_manager  Asset manager object.
	 */
	public function __construct(
		TranslationsAPI $translations_api,
		AssetManager $asset_manager
	) {

		$this->translations_api = $translations_api;

		$this->asset_manager = $asset_manager;
	}

	/**
	 * Wires up all functions.
	 *
	 * @return void
	 */
	public function initialize( ) {

		if ( ! is_admin() ) {
			add_filter( 'the_content', [ $this, 'frontend_tab' ] );
		}
	}

	/**
	 * Create the tab and prepend it to the body tag.
	 *
	 * @wp-hook the_content
	 *
	 * @param string $content HTML content.
	 *
	 * @return string
	 */
	public function frontend_tab( $content ) {

		$translations = $this->get_translations();
		if ( ! $translations ) {
			return $content;
		}

		$this->asset_manager->enqueue_style( 'multilingualpress' );

		$current_blog_id = get_current_blog_id();

		$translated = [];

		foreach ( $translations as $site => $translation ) {
			if ( $current_blog_id !== $site ) {
				$translated[ $translation->remote_url() ] = $translation->language();
			}
		}

		// TODO: Use repository here.
		$option = get_site_option( 'inpsyde_multilingual_quicklink_options' );

		$position = isset( $option['mlp_quicklink_position'] ) ? $option['mlp_quicklink_position'] : 'tr';

		$switcher = $this->to_html( $translated, $position );

		if ( 't' === $position[0] ) {
			// Position at the top.
			return $switcher . $content;
		}

		// Position at the bottom.
		return $content . $switcher;
	}

	/**
	 * Returns the translations.
	 *
	 * @return Translation[]
	 */
	private function get_translations() {

		if ( isset( $this->translations ) ) {
			return $this->translations;
		}

		$this->translations = is_singular()
			? $this->translations_api->get_translations( [
				'type' => Request::TYPE_SINGULAR,
			] )
			: [];

		return $this->translations;
	}

	/**
	 * Converts the list of translated posts into HTML.
	 *
	 * @param Language[] $translated Translated posts.
	 * @param string     $position   Position of the quicklinks tab.
	 *
	 * @return string
	 */
	protected function to_html( array $translated, $position ) {

		if ( 4 > count( $translated ) ) {
			$type = 'links';
			$element = 'a';
			$glue = '<br>';
			$container = 'links';
		} else {
			$type = 'options';
			$element = 'option';
			$glue = '';
			$container = 'form';
		}

		$elements = [];

		foreach ( $translated as $url => $language ) {
			if ( 'links' === $type ) {
				$attributes = [
					'href'     => $url,
					'hreflang' => $language->name( 'http' ),
					'rel'      => 'alternate',
				];
			} else {
				$attributes = [ 'value' => $url, ];
			}

			$attributes_html = '';

			foreach ( $attributes as $key => $value ) {
				$attributes_html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}

			$elements[] = sprintf(
				'<%1$s%2$s>%3$s</%1$s>',
				$element,
				$attributes_html,
				$language->name( 'native' )
			);
		}

		$html = implode( $glue, $elements );

		return $this->get_html_container( $html, $container, $translated, $position );
	}

	/**
	 * Returns the remote post links in form of up to three link elements, or a select element for more than three
	 * links.
	 *
	 * @param  string $selections 'option' or 'a' elements.
	 * @param  string $type       'links' or 'form'.
	 * @param  array  $translated Original array of translated posts, passed to the filter.
	 * @param  string $position   Quicklink position.
	 *
	 * @return string
	 */
	protected function get_html_container( $selections, $type, $translated, $position ) {

		$class_inner = 'mlp_inner';

		$label_text = esc_html_x( 'Read in:', 'Quicklink label', 'multilingual-press' );

		if ( 'links' === $type ) {
			$html = <<<HTML
<div class="mlp-quicklinks mlp-quicklink-links $position mlp_quicklinks mlp_quicklinks_links">
	<div class="$class_inner">
		$label_text<br>
		$selections
	</div>
</div>
HTML;
		} else {
			$home_url = home_url();
			$home_url = esc_attr( $home_url );

			$select_id   = 'mlp-quicklink-select';
			$select_name = 'mlp_quicklink_select';

			$submit_text = esc_attr_x( 'Go', 'quicklink submit button', 'multilingual-press' );

			$html = <<<HTML
<form action="$home_url" method="post" class="mlp-quicklinks mlp-quicklink-form $position mlp_quicklinks mlp_quicklinks_form">
	<div class="$class_inner">
		<label for="$select_id">
			$label_text
			<select name="$select_name" id="$select_id" autocomplete="off">
				$selections
			</select>
		</label>
		<input type="submit" value="$submit_text">
	</div>
</form>
HTML;

			$this->asset_manager->enqueue_script( 'multilingualpress' );
		}

		/**
		 * Filters the quicklinks HTML.
		 *
		 * @param string $html       HTML output.
		 * @param string $type       Quicklink type, 'links' or 'form'.
		 * @param array  $translated Array of translated posts.
		 * @param string $selections Selections, 'option' or 'a' elements.
		 * @param string $position   Quicklink position.
		 */
		return (string) apply_filters(
			'mlp_quicklinks_html',
			$html,
			$type,
			$translated,
			$selections,
			$position
		);
	}
}
