<?php # -*- coding: utf-8 -*-
/**
 * Mlp_Term_Field_View
 *
 * @version 2014.09.17
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Term_Field_View {

	/**
	 * @type string
	 */
	const ADD_TERM_TITLE = 'add_term_title';

	/**
	 * @type string
	 */
	const ADD_TERM_FIELDS = 'add_term_fields';

	/**
	 * @type string
	 */
	const ADD_TERM_FIELDSET_ID = 'add_term_fieldset_id';

	/**
	 * @type string
	 */
	const EDIT_TERM_TITLE = 'edit_term_title';

	/**
	 * @type string
	 */
	const EDIT_TERM_FIELDS = 'edit_term_fields';

	/**
	 * @type Mlp_Updatable
	 */
	private $updatable;

	/**
	 * @param Mlp_Updatable $updatable
	 */
	public function __construct( Mlp_Updatable $updatable ) {

		$this->updatable = $updatable;
	}

	/**
	 * Template for an extra row in the "edit term" form.
	 *
	 * @return void
	 */
	public function edit_term() {

		?>
		<tr class="form-field">
			<th scope="row"><?php
				$this->updatable->update( self::EDIT_TERM_TITLE );
				?></th>
			<td>
				<?php
				$this->updatable->update( self::EDIT_TERM_FIELDS );
				?>
			</td>
		</tr>
	<?php

	}

	/**
	 * Template for an extra field in the "Add new term" form.
	 *
	 * @return void
	 */
	public function add_term() {

		?>
		<fieldset id="<?php
		$this->updatable->update( self::ADD_TERM_FIELDSET_ID );
		?>">
			<legend><?php
				$this->updatable->update( self::ADD_TERM_TITLE );
				?></legend>
			<?php
			$this->updatable->update( self::ADD_TERM_FIELDS );
			?>
		</fieldset>
	<?php
	}
}