<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Language manager settings page view.
 *
 * @package Inpsyde\MultilingualPress\LanguageManager
 * @since   3.0.0
 */
final class LanguageManagerSettingsPageView implements SettingsPageView {

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Nonce        $nonce         Nonce object.
	 * @param AssetManager $asset_manager Asset manager object.
	 */
	public function __construct( Nonce $nonce, AssetManager $asset_manager ) {

		$this->nonce = $nonce;

		$this->asset_manager = $asset_manager;
	}

	/**
	 * Renders the markup.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render() {

		$this->asset_manager->enqueue_style( 'multilingualpress-admin' );

		// TODO: Put the action somewhere, preferably on the language updater (or repository).
		$action = 'update_multilingualpress_languages';

		// TODO: Completely adapt to your needs.
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<form method="post" action="<?php echo admin_url( "admin-post.php?action={$action}" ); ?>">
				<?php echo nonce_field( $this->nonce ); ?>

				<?php
				/**
				 * TODO: Put some nice new code here that does in the end what the following old code does. :)
				 */
				?>

			</form>
		</div>
		<?php
	}
}
