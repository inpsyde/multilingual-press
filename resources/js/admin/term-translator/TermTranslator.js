(function( $, MultilingualPress ) {
	'use strict';

	var TermTranslator = Backbone.View.extend( /** @lends TermTranslator# */ {
		/**
		 * @constructs TermTranslator
		 * @classdesc MultilingualPress TermTranslator module.
		 * @extends Backbone.View
		 */
		initialize: function() {
			/**
			 * The jQuery object representing the MultilingualPress term selects.
			 * @type {jQuery}
			 */
			this.$selects = this.$el.find( 'select' );

			/**
			 * Flag to indicate an ongoing term propagation.
			 * @type {boolean}
			 */
			this.isPropagating = false;
		},

		/**
		 * Propagates the new value of one term select element to all other term select elements.
		 * @param {Event} event - The change event of a term select element.
		 */
		propagateSelectedTerm: function( event ) {
			var $select,
				relation;

			if ( this.isPropagating ) {
				return;
			}

			this.isPropagating = true;

			$select = $( event.target );

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
		 * @param {jQuery} $select - A select element.
		 * @returns {string} The relation of the selected term.
		 */
		getSelectedRelation: function( $select ) {
			return $select.find( 'option:selected' ).data( 'relation' ) || '';
		},

		/**
		 * Sets the given select element's value to that of the option with the given relation, or the first option.
		 * @param {jQuery} $select - A select element.
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

	// Register the TermTranslator module for the Edit Tags admin page.
	MultilingualPress.registerModule( 'edit-tags.php', 'TermTranslator', TermTranslator, {
		el: '#mlp-term-translations',
		events: {
			'change select': 'propagateSelectedTerm'
		}
	} );
})( jQuery, window.MultilingualPress );
