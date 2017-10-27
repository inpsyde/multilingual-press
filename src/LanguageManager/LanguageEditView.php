<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\Common\Labels;
use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

use function Inpsyde\MultilingualPress\get_available_languages;

final class LanguageEditView {
	/**
	 * @var Languages
	 */
	private $languages;

	/**
	 * @var Labels
	 */
	private $labels;

	/**
	 * @var Language
	 */
	private $language;

	/**
	 * LanguageEditView constructor.
	 *
	 * @param Languages $languages
	 * @param Labels    $labels
	 */
	public function __construct( Languages $languages, Labels $labels )
	{
		$this->languages = $languages;
		$this->labels    = $labels;
	}

	/**
	 * @param int $language_id
	 *
	 * @return void
	 */
	public function render( int $language_id )
	{
		$this->language = $this->languages->get_language_by( LanguagesTable::COLUMN_ID, $language_id );

		print $this->print_language_id( $this->language );
		print '<table>';
		$this->print_text_fields( $this->language );
		$this->print_priority( $this->language );
		$this->print_rtl( $this->language );
		$this->print_site_selector( $this->language );
		print '</table>';

		if ( ! wp_doing_ajax() ) {
			submit_button();
		}
	}

	/**
	 * Language ID in a hidden input
	 *
	 * @return void
	 */
	private function print_language_id()
	{
		?>
		<input type="hidden" name="language_id" value="<?=esc_attr( $this->language->name( LanguagesTable::COLUMN_ID ) )?>">
		<?php
	}

	/**
	 * General text input fields
	 *
	 * @return void
	 */
	private function print_text_fields()
	{
		$text_fields = [
			LanguagesTable::COLUMN_NATIVE_NAME,
			LanguagesTable::COLUMN_ENGLISH_NAME,
			LanguagesTable::COLUMN_HTTP_CODE,
			LanguagesTable::COLUMN_ISO_639_1_CODE,
			LanguagesTable::COLUMN_LOCALE,
		];

		foreach ( $text_fields as $field ) {
			$label = $this->labels->label( $field );
			$value = $this->language->name( $field );
			?>
			<tr>
				<td class="alignright">
					<label for="<?= esc_attr( $field ) ?>_id">
						<?= esc_html( $label ) ?>
					</label>
				</td>
				<td>
					<input
						type="text"
						name="<?= esc_attr( $field ) ?>"
						id="<?= esc_attr( $field ) ?>_id"
						value="<?= esc_attr( $value ) ?>"
					/>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * HTTP priority numeric input
	 *
	 * @return void
	 */
	private function print_priority()
	{
		?>
		<tr>
			<td class="alignright">
				<label for="<?=esc_attr( LanguagesTable::COLUMN_PRIORITY )?>_id">
					<?=esc_html( $this->labels->label( LanguagesTable::COLUMN_PRIORITY ) )?>
				</label>
			</td>
			<td>
				<input
					type="number" min="1" max="10"
					name="<?=esc_attr( LanguagesTable::COLUMN_PRIORITY )?>"
					id="<?=esc_attr( LanguagesTable::COLUMN_PRIORITY )?>_id"
					value="<?=esc_attr( $this->language->name( LanguagesTable::COLUMN_PRIORITY ) )?>"
				/>
			</td>
		</tr>
		<?php
	}

	/**
	 * Checkbox for RTL property of a language
	 *
	 * @return void
	 */
	private function print_rtl()
	{
		?>
		<tr>
			<td></td>
			<td>
				<input
					type="checkbox"
					name="<?=esc_attr( LanguagesTable::COLUMN_RTL )?>"
					id="<?=esc_attr( LanguagesTable::COLUMN_RTL )?>_id"
					value="1"
					<?=checked( true, $this->language->is_rtl(), false )?>
				/>
				<label for="<?=esc_attr( LanguagesTable::COLUMN_RTL )?>_id">
					<?=esc_html( $this->labels->label( LanguagesTable::COLUMN_RTL ) )?>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * List of sites to assign a language
	 *
	 * @return void
	 */
	private function print_site_selector()
	{
		$sites = get_sites();
		$active_languages = get_available_languages( false );
		?>
		<tr>
			<td class="alignright">
				<?=esc_html_e( 'Sites', 'multilingualpress' )?>
			</td>
			<td>
				<em><?=esc_html_e( 'Assign one or more sites in order to activate the language', 'multilingualpress')?></em>
				<br />
				<?php
				/** @var \WP_Site $site */
				foreach ( $sites as $site ) {
					$active = $this->is_active_language_for_site( $site, $active_languages );
					?>
					<label>
						<input
							type="checkbox"
							name="sites[<?=esc_attr( $site->blog_id )?>]"
							value="1"
							<?php checked( true, $active )?>
						/>
						<?=esc_html( $site->blogname )?>
						<code><?=esc_html( $site->domain . $site->path )?></code>
					</label>
					<br />
					<?php
				}
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param \WP_Site $site
	 * @param array    $active_languages
	 *
	 * @return bool
	 */
	private function is_active_language_for_site( \WP_Site $site, array $active_languages )
	{
		if ( ! isset ( $active_languages[ $site->blog_id ] ) ) {
			return false;
		}

		$replaced = str_replace( '_', '-', $active_languages[ $site->blog_id ] );

		return $this->language->name( LanguagesTable::COLUMN_HTTP_CODE ) === $replaced;
	}
}