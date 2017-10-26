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
	 * @var \Inpsyde\MultilingualPress\API\Languages
	 */
	private $languages;

	/**
	 * @var \Inpsyde\MultilingualPress\Common\Labels
	 */
	private $labels;

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
		$language = $this->languages->get_language_by( LanguagesTable::COLUMN_ID, $language_id );

		print $this->print_language_id( $language );
		print '<table>';
		$this->print_text_fields( $language );
		$this->print_priority( $language );
		$this->print_rtl( $language );
		$this->print_site_selector();
		print '</table>';

		if ( ! wp_doing_ajax() ) {
			submit_button();
		}
	}

	private function print_language_id( Language $language )
	{
		?>
		<input type="hidden" name="language_id" value="<?=esc_attr($language->name( LanguagesTable::COLUMN_ID ) )?>">
		<?php
	}

	/**
	 * @param Language $language
	 *
	 * @return void
	 */
	private function print_text_fields( Language $language )
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
			$value = $language->name( $field );
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
	 * @param Language $language
	 *
	 * @return void
	 */
	private function print_priority( Language $language )
	{
		?>
		<tr>
			<td class="alignright">
				<label for="<?=esc_attr( LanguagesTable::COLUMN_PRIORITY )?>_id">
					<?=esc_attr( $this->labels->label( LanguagesTable::COLUMN_PRIORITY ) )?>
				</label>
			</td>
			<td>
				<input
					type="number" min="1" max="10"
					name="<?=esc_attr( LanguagesTable::COLUMN_PRIORITY )?>"
					id="<?=esc_attr( LanguagesTable::COLUMN_PRIORITY )?>_id"
					value="<?=esc_html( $language->name( LanguagesTable::COLUMN_PRIORITY ) )?>"
				/>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param Language $language
	 *
	 * @return void
	 */
	private function print_rtl( Language $language )
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
					<?=checked( true, $language->is_rtl(), false )?>
				/>
				<label for="<?=esc_attr( LanguagesTable::COLUMN_RTL )?>_id">
					<?=esc_html( $this->labels->label( LanguagesTable::COLUMN_RTL ) )?>
				</label>
			</td>
		</tr>
		<?php
	}

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
					$active = isset ( $active_languages[ $site->blog_id ] );
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
}