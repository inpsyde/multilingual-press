<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Setting\User;

/**
 * User setting view.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\User
 * @since   3.0.0
 */
class UserSettingView {

	/**
	 * @var bool
	 */
	private $check_user;

	/**
	 * @var UserSettingViewModel
	 */
	private $model;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param UserSettingViewModel $model      View model object.
	 * @param bool                 $check_user Optional. Only render for users capable of editing? Defaults to true.
	 */
	public function __construct( UserSettingViewModel $model, $check_user = true ) {

		$this->model = $model;

		$this->check_user = (bool) $check_user;
	}

	/**
	 * Renders the user setting markup.
	 *
	 * @since   3.0.0
	 * @wp-hook personal_options
	 *
	 * @param \WP_User $user User object.
	 *
	 * @return bool Whether or not the user setting markup was rendered successfully.
	 */
	public function render( \WP_User $user ) {

		if ( $this->check_user && ! current_user_can( 'edit_user', $user->ID ) ) {
			return false;
		}
		?>
		<tr>
			<th scope="row">
				<?php echo $this->model->title(); ?>
			</th>
			<td>
				<?php echo $this->model->markup( $user ); ?>
			</td>
		</tr>
		<?php

		return true;
	}
}
