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
		Mlp_Updatable $watcher,
		Mlp_Browsable $pagination_data
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

		$title = $this->page_data->get_title();

		$action = $this->page_data->get_form_action();

		$action_name = $this->page_data->get_action_name();

		$paged = $this->pagination_data->get_current_page();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php $this->watcher->update( 'before_form' ); ?>
			<form action="<?php echo esc_attr( $action ); ?>" method="post">
				<input type="hidden" name="action" value="<?php echo esc_attr( $action_name ); ?>">
				<input type="hidden" name="paged" value="<?php echo esc_attr( $paged ); ?>">
				<?php
					wp_nonce_field( $this->page_data->get_nonce_action(), $this->page_data->get_nonce_name() );

					$this->watcher->update( 'before_table' );

					$this->watcher->update( 'show_table' );

					$this->watcher->update( 'after_table' );

					submit_button(
						esc_attr__( 'Save changes', 'multilingual-press' ),
						'primary',
						'save',
						false,
						array(
							'style' => 'float:left',
						)
					);

					$this->watcher->update( 'after_form_submit_button' );
				?>
			</form>
			<?php $this->watcher->update( 'after_form' ); ?>
		</div>
		<?php
	}
}
