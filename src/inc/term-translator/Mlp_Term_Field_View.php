<?php # -*- coding: utf-8 -*-

/**
 * Mlp_Term_Field_View
 *
 * @version 2015.06.29
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Term_Field_View {

	/**
	 * @type string
	 */
	const EDIT_TERM_BEFORE = 'edit_term_before';

	/**
	 * @type string
	 */
	const EDIT_TERM_AFTER = 'edit_term_after';

	/**
	 * @type string
	 */
	const ADD_TERM_BEFORE = 'add_term_before';

	/**
	 * @type string
	 */
	const ADD_TERM_AFTER = 'add_term_after';

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
	 * @var Mlp_Updatable
	 */
	private $updatable;

	/**
	 * @param Mlp_Updatable $updatable
	 */
	public function __construct( Mlp_Updatable $updatable ) {

		$this->updatable = $updatable;
	}

	/**
	 * Template for an extra row in the "Edit term" form.
	 *
	 * @return void
	 */
	public function edit_term() {

		$this->updatable->update( self::EDIT_TERM_BEFORE );

		$title = $this->updatable->update( self::EDIT_TERM_TITLE );
		?>
		<tr class="form-field">
			<th scope="row"><?php echo esc_html( $title ); ?></th>
			<td><?php $this->updatable->update( self::EDIT_TERM_FIELDS ); ?></td>
		</tr>
		<?php
		$this->updatable->update( self::EDIT_TERM_AFTER );
	}

	/**
	 * Template for an extra field in the "Add new term" form.
	 *
	 * @return void
	 */
	public function add_term() {

		$this->updatable->update( self::ADD_TERM_BEFORE );

		$id = $this->updatable->update( self::ADD_TERM_FIELDSET_ID );

		$title = $this->updatable->update( self::ADD_TERM_TITLE );
		?>
		<fieldset class="form-field" id="<?php echo esc_attr( $id ); ?>">
			<legend><?php echo esc_html( $title ); ?></legend>
			<?php $this->updatable->update( self::ADD_TERM_FIELDS ); ?>
		</fieldset>
		<?php
		$this->updatable->update( self::ADD_TERM_AFTER );
	}

}
