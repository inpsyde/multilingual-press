const { jQuery: $ } = window;

/**
 * MultilingualPress TermTranslator module.
 */
class TermTranslator extends Backbone.View {
	/**
	 * Selects the create term operation for the according site.
	 * @param {Event} event - The input event of a create term input element.
	 */
	handleTermInput( event ) {
		this.setTermOperation( Number( $( event.target ).data( 'site' ) ), 'create' );
	}

	/**
	 * Selects the select term operation for the according site.
	 * @param {Event} event - The change event of a term select element.
	 */
	handleTermSelection( event ) {
		this.setTermOperation( Number( $( event.target ).data( 'site' ) ), 'select' );
	}

	/**
	 * Sets the term operation for the site with the given ID to the given value.
	 * @param {Number} siteId - The site ID.
	 * @param {String} operation - The term operation.
	 */
	setTermOperation( siteId, operation ) {
		$( `#mlp_related_term_op-${siteId}-${operation}` ).prop( 'checked', true );
	}
}

export default TermTranslator;
