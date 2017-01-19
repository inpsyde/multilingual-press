<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\Site;

/**
 * Site setting view implementation for a whole settings section.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\Site
 * @since   3.0.0
 */
final class SiteSettingsSectionView implements SiteSettingView {

	/**
	 * Action hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_AFTER = 'multilingualpress.after_site_settings';

	/**
	 * Action hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_BEFORE = 'multilingualpress.before_site_settings';

	/**
	 * @var SiteSettingsSectionViewModel
	 */
	private $model;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingsSectionViewModel $model Site settings section view model object.
	 */
	public function __construct( SiteSettingsSectionViewModel $model ) {

		$this->model = $model;
	}

	/**
	 * Renders the site setting markup.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	public function render( $site_id ) {

		echo $this->model->title();
		?>
		<table class="form-table">
			<?php
			$model_id = $this->model->id();

			/**
			 * Fires right before the settings are rendered.
			 *
			 * @since 3.0.0
			 *
			 * @param int    $site_id  Site ID.
			 * @param string $model_id Model ID.
			 */
			do_action( static::ACTION_BEFORE, $site_id, $model_id );

			/**
			 * Fires right before the settings are rendered.
			 *
			 * @since 3.0.0
			 *
			 * @param int $site_id Site ID.
			 */
			do_action( static::ACTION_BEFORE . "_{$model_id}", $site_id );

			$this->model->render_view( $site_id );

			/**
			 * Fires right after the settings have been rendered.
			 *
			 * @since 3.0.0
			 *
			 * @param int    $site_id  Site ID.
			 * @param string $model_id Model ID.
			 */
			do_action( static::ACTION_AFTER, $site_id, $model_id );

			/**
			 * Fires right after the settings have been rendered.
			 *
			 * @since 3.0.0
			 *
			 * @param int $site_id Site ID.
			 */
			do_action( static::ACTION_AFTER . "_{$model_id}", $site_id );
			?>
		</table>
		<?php
	}
}
