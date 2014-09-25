<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Language_Manager_Page_View
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Manager_Page_View {

	/**
	 * @var Mlp_Options_Page_Data
	 */
	private $page_data;

	/**
	 * @var Mlp_Browsable
	 */
	private $pagination_data;

	/**
	 * @var Mlp_Updatable
	 */
	private $watcher;

	/**
	 * @param Mlp_Options_Page_Data $page_data
	 * @param Mlp_Updatable         $watcher
	 * @param Mlp_Browsable         $pagination_data
	 */
	public function __construct(
		Mlp_Options_Page_Data $page_data,
		Mlp_Updatable         $watcher,
		Mlp_Browsable         $pagination_data
	) {
		$this->watcher         = $watcher;
		$this->page_data       = $page_data;
		$this->pagination_data = $pagination_data;
	}

	/**
	 * Callback for page output.
	 *
	 */
	public function render() {

		?>
		<div class="wrap">
			<?php
			print '<h2>' . $this->page_data->get_title() . '</h2>';

			$this->watcher->update( 'before_form' );
			?>
			<form action="<?php echo $this->page_data->get_form_action(); ?>" method="post">
				<input type="hidden" name="action" value="<?php echo $this->page_data->get_action_name(); ?>" />
				<input type="hidden" name="paged" value="<?php echo $this->pagination_data->get_current_page(); ?>" />
			<?php
				wp_nonce_field(
					$this->page_data->get_nonce_action(),
					$this->page_data->get_nonce_name()
				);
				$this->watcher->update( 'before_table' );
				$this->watcher->update( 'show_table' );
				$this->watcher->update( 'after_table' );

				submit_button(
					esc_attr__( 'Save changes', 'multilingualpress' ),
					'primary',
					'save',
					FALSE,
					array( 'style' => 'float:left')
				);
				$this->watcher->update( 'after_form_submit_button' );
			?>
			</form>
			<?php
			$this->watcher->update( 'after_form' );
			?>
		</div>
		<?php
	}
}