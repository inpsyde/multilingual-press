/* global MultilingualPress */
(function( $ ) {
	'use strict';

	var TermTranslator = {

		/**
		 * Initializes the TermTranslator module.
		 */
		initialize: function() {

			TermTranslator.$table = $( '.mlp_term_selections' );
			if ( TermTranslator.$table.length ) {
				TermTranslator.$selects = TermTranslator.$table.find( 'select' );

				TermTranslator.$table.on( 'change', 'select', function() {

					TermTranslator.propagateSelectedTerm( $( this ) );
				} );
			}
		},

		/**
		 * Propagates the current value of the given select element to all other term select elements.
		 * @param {Object} $select - The select element.
		 */
		propagateSelectedTerm: function( $select ) {

			var relation;

			if ( TermTranslator.isPropagating ) {
				return;
			}

			TermTranslator.isPropagating = true;

			relation = TermTranslator.getSelectedRelation( $select );
			if ( '' !== relation ) {
				TermTranslator.$selects.not( $select ).each( function( index, element ) {

					TermTranslator.selectTerm( $( element ), relation );
				} );
			}

			TermTranslator.isPropagating = false;
		},

		/**
		 * Returns the relation of the given select element (i.e., its currently selected option).
		 * @param {Object} $select - The select element.
		 * @returns {string} - The relation.
		 */
		getSelectedRelation: function( $select ) {

			return $select.find( 'option:selected' ).data( 'relation' ) || '';
		},

		/**
		 * Sets the given select element's value to those of the option with the given relation, or the first option.
		 * @param {Object} $select - The select element.
		 * @param {string} relation - The relation.
		 */
		selectTerm: function( $select, relation ) {

			var $option = $select.find( 'option[data-relation="' + relation + '"]' );
			if ( $option.length ) {
				$select.val( $option.val() );
			} else if ( TermTranslator.getSelectedRelation( $select ) ) {
				$select.val( $select.find( 'option' ).first().val() );
			}
		}
	};

	MultilingualPress.TermTranslator = TermTranslator;

	$( MultilingualPress.TermTranslator.initialize );
})( jQuery );
