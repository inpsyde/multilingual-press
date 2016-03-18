<?php
/**
 * Table markup view for user profile page.
 *
 * @version 2014.07.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_User_Settings_Container_Html {

	/**
	 * @var Mlp_User_Settings_View_Interface
	 */
	private $details;

	/**
	 * @param Mlp_User_Settings_View_Interface $details
	 */
	public function __construct( Mlp_User_Settings_View_Interface $details ) {

		$this->details = $details;
	}

	/**
	 * @param WP_User $user
	 * @return void
	 */
	public function render( WP_User $user ) {

		?>
		<tr>
			<th scope="row">
				<?php $this->details->show_header( $user ); ?>
			</th>
			<td>
				<?php $this->details->show_content( $user ); ?>
			</td>
		</tr>
		<?php
	}
}
