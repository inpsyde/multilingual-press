const $ = window.jQuery;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
const _this = {
	/**
	 * Flag to indicate an ongoing term propagation.
	 * @type {Boolean}
	 */
	isPropagating: false
};

/**
 * MultilingualPress TermTranslator module.
 */
class TermTranslator extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * The jQuery object representing the MultilingualPress term selects.
		 * @type {jQuery}
		 */
		_this.$selects = this.$el.find( 'select' );
	}

	/**
	 * Returns the jQuery object representing the MultilingualPress term selects.
	 * @returns {jQuery}
	 */
	get $selects() {
		return _this.$selects;
	}

	/**
	 * Propagates the new value of one term select element to all other term select elements.
	 * @param {Event} event - The change event of a term select element.
	 */
	propagateSelectedTerm( event ) {
		let $select,
			relation;

		if ( _this.isPropagating ) {
			return;
		}

		_this.isPropagating = true;

		$select = $( event.target );

		relation = this.getSelectedRelation( $select );
		if ( '' !== relation ) {
			this.$selects.not( $select ).each( ( index, element ) => this.selectTerm( $( element ), relation ) );
		}

		_this.isPropagating = false;
	}

	/**
	 * Returns the relation of the given select element (i.e., its currently selected option).
	 * @param {jQuery} $select - A select element.
	 * @returns {String} The relation of the selected term.
	 */
	getSelectedRelation( $select ) {
		return $select.find( 'option:selected' ).data( 'relation' ) || '';
	}

	/**
	 * Sets the given select element's value to that of the option with the given relation, or the first option.
	 * @param {jQuery} $select - A select element.
	 * @param {String} relation - The relation of a term.
	 * @returns {Boolean} Whether or not a term was selected.
	 */
	selectTerm( $select, relation ) {
		const $option = $select.find( 'option[data-relation="' + relation + '"]' );

		if ( $option.length ) {
			$select.val( $option.val() );

			return true;
		} else if ( this.getSelectedRelation( $select ) ) {
			$select.val( $select.find( 'option' ).first().val() );

			return true;
		}

		return false;
	}
}

export default TermTranslator;
