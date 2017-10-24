<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Language manager settings page view.
 *
 * @package Inpsyde\MultilingualPress\LanguageManager
 * @since   3.0.0
 */
final class LanguageManagerSettingsPageView implements SettingsPageView {

	const ACTION_CONTENT_DISPLAY         = 'multilingualpress_language_manager_default';
	const ACTION_SINGLE_LANGUAGE_DISPLAY = 'multilingualpress_display_single_language';
	const CURRENT_SCREEN                 = 'multilingualpress_language_manager';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var ServerRequest
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Nonce         $nonce         Nonce object.
	 * @param AssetManager  $asset_manager Asset manager object.
	 * @param ServerRequest $request
	 */
	public function __construct( Nonce $nonce, AssetManager $asset_manager, ServerRequest $request )
	{
		$this->nonce         = $nonce;
		$this->asset_manager = $asset_manager;
		$this->request       = $request;
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

		$action = LanguageManagerUpdater::ACTION;

		// TODO: Completely adapt to your needs.
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<form method="post" action="<?php echo admin_url( "admin-post.php?action={$action}" ); ?>">
				<?php
				nonce_field( $this->nonce );
				$this->fire_actions();
				?>

			</form>
		</div>
		<?php
	}

	private function fire_actions()
	{
		$language_id = (string) $this->request->body_value(
			LanguagesTable::COLUMN_ID,
			INPUT_GET,
			FILTER_VALIDATE_INT
		);

		if ( $language_id ) {
			/**
			 * Show edit screen for a single language.
			 *
			 * This action is called either for a single page view, an edit form
			 * loaded per AJAX, or when a new language is added.
			 *
			 * @since 3.0.0
			 * @param string $language_id A positive integer as a string.
			 *                            It is 0 when a new language is added.
			 */
			do_action( self::ACTION_SINGLE_LANGUAGE_DISPLAY, $language_id );
			return;
		}
		/**
		 * Show language manager main screen.
		 *
		 * Normally used to show the list table with active languages.
		 * @since 3.0.0
		 */
		do_action( self::ACTION_CONTENT_DISPLAY );
	}
}
