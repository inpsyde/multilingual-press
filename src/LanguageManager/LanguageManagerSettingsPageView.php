<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\LanguageManager;

use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Language manager settings page view.
 *
 * @package Inpsyde\MultilingualPress\LanguageManager
 * @since   3.0.0
 */
final class LanguageManagerSettingsPageView implements SettingsPageView {

	const CONTENT_DISPLAY = 'display_language_manager';
	const SINGLE_LANGUAGE_DISPLAY = 'display_single_language';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var LanguageListTable
	 */
	private $listTable;

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

				$langID = $this->request->body_value( 'langID', INPUT_GET, FILTER_VALIDATE_INT );
				if ( $langID ) {
					do_action( self::SINGLE_LANGUAGE_DISPLAY, $langID );
				}
				else {
					do_action( self::CONTENT_DISPLAY );
				}
				?>

			</form>
		</div>
		<?php
	}
}
