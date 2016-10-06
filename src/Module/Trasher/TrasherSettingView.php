<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Trasher;

/**
 * Trasher setting view.
 *
 * @package Inpsyde\MultilingualPress\Module\Trasher
 * @since   3.0.0
 */
class TrasherSettingView {

	/**
	 * @var TrasherSettingRepository
	 */
	private $setting_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TrasherSettingRepository $setting_repository Trasher setting repository object.
	 */
	public function __construct( TrasherSettingRepository $setting_repository ) {

		$this->setting_repository = $setting_repository;
	}

	/**
	 * Renders the setting markup.
	 *
	 * @since   3.0.0
	 * @wp-hook post_submitbox_misc_actions
	 *
	 * @return void
	 */
	public function render() {

		$name = TrasherSettingRepository::META_KEY;

		$id = 'trasher';

		// TODO: Use a nonce here! This makes the hidden field below then superfluous.
		?>
		<div class="misc-pub-section curtime misc-pub-section-last">
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="0">
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>"
					value="1" id="<?php echo esc_attr( $id ); ?>" <?php checked( $this->setting_repository->get() ); ?>>
				<?php _e( 'Send all the translations to trash when this post is trashed.', 'multilingual-press' ); ?>
			</label>
		</div>
		<?php
	}
}
