<?php
/**
 * Interface used by Mlp_Extra_General_Settings_Box.
 *
 * @version 2014.03.03
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */

interface Mlp_Extra_General_Settings_Box_Data_Interface extends Mlp_Updatable {

	/**
	 * Get box title.
	 *
	 * Will be wrapped in h4 tags by the view if it is not empty.
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Get the box description.
	 *
	 * Will be enclosed in p tags by the view, so make sure the markup
	 * is valid afterwards.
	 *
	 * @return string
	 */
	public function get_main_description();

	/**
	 * The ID used in the main form element.
	 *
	 * Used to wrap the description in a label element, so it is accessible for
	 * screen reader users.
	 *
	 * @return string
	 */
	public function get_main_label_id();

	/**
	 * Value for ID attribute for the box.
	 *
	 * @return string
	 */
	public function get_box_id();
}