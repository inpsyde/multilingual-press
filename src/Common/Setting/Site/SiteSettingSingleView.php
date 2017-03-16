<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\Site;

/**
 * Site setting view implementation for a single setting.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\Site
 * @since   3.0.0
 */
final class SiteSettingSingleView implements SiteSettingView  {

	/**
	 * @var bool
	 */
	private $check_user;

	/**
	 * @var SiteSettingViewModel
	 */
	private $model;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingViewModel $model      View model object.
	 * @param bool                 $check_user Optional. Only render for users capable of editing? Defaults to true.
	 */
	public function __construct( SiteSettingViewModel $model, $check_user = true ) {

		$this->model = $model;

		$this->check_user = (bool) $check_user;
	}

	/**
	 * Renders the site setting markup.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the site setting markup was rendered successfully.
	 */
	public function render( $site_id ) {

		if ( $this->check_user && ! current_user_can( 'manage_sites' ) ) {
			return false;
		}
		?>
		<tr class="form-field">
			<th scope="row">
				<?php echo $this->model->title(); ?>
			</th>
			<td>
				<?php echo $this->model->markup( (int) $site_id ); ?>
			</td>
		</tr>
		<?php

		return true;
	}
}
