<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Type\Setting;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Class Mlp_Language_Manager_Page_View
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Manager_Page_View {

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var Mlp_Browsable
	 */
	private $pagination_data;

	/**
	 * @var Setting
	 */
	private $setting;

	/**
	 * @var Mlp_Updatable
	 */
	private $watcher;

	/**
	 * @param Setting       $setting
	 * @param Mlp_Updatable $watcher
	 * @param Mlp_Browsable $pagination_data
	 * @param Nonce         $nonce           Nonce object.
	 */
	public function __construct(
		Setting $setting,
		Mlp_Updatable $watcher,
		Mlp_Browsable $pagination_data,
		Nonce $nonce
	) {

		$this->setting = $setting;

		$this->watcher = $watcher;

		$this->pagination_data = $pagination_data;

		$this->nonce = $nonce;
	}

	/**
	 * Callback for page output.
	 *
	 */
	public function render() {

		$current_page = $this->pagination_data->get_current_page();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $this->setting->title() ); ?></h1>
			<?php $this->watcher->update( 'before_form' ); ?>
			<form action="<?php echo esc_url( $this->setting->url() ); ?>" method="post">
				<input type="hidden" name="action" value="<?php echo esc_attr( $this->setting->action() ); ?>">
				<input type="hidden" name="paged" value="<?php echo esc_attr( $current_page ); ?>">
				<?php echo nonce_field( $this->nonce ); ?>
				<?php $this->watcher->update( 'before_table' ); ?>
				<?php $this->watcher->update( 'show_table' ); ?>
				<?php $this->watcher->update( 'after_table' ); ?>
				<?php submit_button(
					esc_attr__( 'Save changes', 'multilingualpress' ),
					'primary',
					'save',
					false,
					[ 'style' => 'float:left' ]
				); ?>
				<?php $this->watcher->update( 'after_form_submit_button' ); ?>
			</form>
			<?php $this->watcher->update( 'after_form' ); ?>
		</div>
		<?php
	}
}
