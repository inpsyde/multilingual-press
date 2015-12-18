/* global MultilingualPress */
(function( $ ) {
	'use strict';

	var TermTranslator = Backbone.View.extend( {
		el: '.mlp_term_selections',

		events: {
			'change select': 'propagateSelectedTerm'
		},

		/**
		 * Initializes the TermTranslator module.
		 */
		initialize: function() {
			if ( this.$el.length ) {
				this.$selects = this.$el.find( 'select' );
			}
		},

		/**
		 * Propagates the value of a term select element to all other term select elements.
		 * @param {Event} event - The change event of a select element.
		 */
		propagateSelectedTerm: function( event ) {
			var $select,
				relation;

			if ( this.isPropagating ) {
				return;
			}

			this.isPropagating = true;

			$select = $( event.currentTarget );

			relation = this.getSelectedRelation( $select );
			if ( '' !== relation ) {
				this.$selects.not( $select ).each( function( index, element ) {
					this.selectTerm( $( element ), relation );
				}.bind( this ) );
			}

			this.isPropagating = false;
		},

		/**
		 * Returns the relation of the given select element (i.e., its currently selected option).
		 * @param {Object} $select - A select element.
		 * @returns {string} - The relation of the selected term.
		 */
		getSelectedRelation: function( $select ) {
			return $select.find( 'option:selected' ).data( 'relation' ) || '';
		},

		/**
		 * Sets the given select element's value to that of the option with the given relation, or the first option.
		 * @param {Object} $select - A select element.
		 * @param {string} relation - The relation of a term.
		 */
		selectTerm: function( $select, relation ) {
			var $option = $select.find( 'option[data-relation="' + relation + '"]' );
			if ( $option.length ) {
				$select.val( $option.val() );
			} else if ( this.getSelectedRelation( $select ) ) {
				$select.val( $select.find( 'option' ).first().val() );
			}
		}
	} );

	MultilingualPress.registerModule( 'edit-tags.php', 'TermTranslator', TermTranslator );
})( jQuery );
