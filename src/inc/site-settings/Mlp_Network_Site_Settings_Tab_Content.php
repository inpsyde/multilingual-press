<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Type\Setting;

/**
 * Content of the per-site settings tab
 *
 * @version 2014.07.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings_Tab_Content {

	/**
	 * @var int
	 */
	private $blog_id;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var Setting
	 */
	private $setting;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param Setting $setting Options page data.
	 * @param int     $blog_id Blog ID
	 * @param Nonce   $nonce   Nonce object.
	 */
	public function __construct(
		Setting $setting,
		$blog_id,
		Nonce $nonce
	) {

		$this->setting = $setting;

		$this->blog_id = $blog_id;

		$this->nonce = $nonce;
	}

	/**
	 * Print tab content and provide two hooks.
	 *
	 * @return void
	 */
	public function render_content() {

		?>
		<form action="<?php echo $this->setting->url(); ?>" method="post">
			<input type="hidden" name="action" value="<?php echo esc_attr( $this->setting->action() ); ?>" />
			<input type="hidden" name="id" value="<?php echo esc_attr( $this->blog_id ); ?>" />
			<?php echo \Inpsyde\MultilingualPress\nonce_field( $this->nonce ); ?>
			<table class="form-table mlp-admin-settings-table">
				<?php
				/**
				 * TODO: Rebuild by using the following new structures:
				 *
				 * ~\Common\Setting\Site\SiteSettingsSectionView (or SiteSettingMultiView) with
				 *
				 * - ~\Core\Admin\LanguageSiteSetting
				 * - ~\Core\Admin\AlternativeLanguageTitleSiteSetting
				 * - ~\Core\Admin\FlagImageURLSiteSetting
				 * - ~\Core\Admin\RelationshipsSiteSetting
				 */

				/**
				 * Runs at the end of but still inside the site settings table.
				 *
				 * @param int $blog_id Blog ID.
				 */
				do_action( 'mlp_blogs_add_fields', $this->blog_id );
				?>
			</table>
			<?php submit_button(); ?>
		</form>
		<?php
	}
}
