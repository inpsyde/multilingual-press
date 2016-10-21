const { jQuery: $ } = window;

/**
 * The MultilingualPress Toggler module.
 */
export class Toggler extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );
	}

	/**
	 * Initializes the given toggler that works by using its individual state.
	 * @param {jQuery} $toggler - The jQuery representation of a toggler element.
	 */
	initializeStateToggler( $toggler ) {
		$( `[name="${$toggler.attr( 'name' )}"]` ).on( 'change', {
			$toggler
		}, this.toggleElementIfChecked );
	}

	/**
	 * Initializes the togglers that work by using their individual state.
	 */
	initializeStateTogglers() {
		$( '.mlp-state-toggler' ).each( ( index, element ) => this.initializeStateToggler( $( element ) ) );
	}

	/**
	 * Toggles the element with the ID given in the according data attribute.
	 * @param {Event} event - The click event of a toggler element.
	 */
	toggleElement( event ) {
		const targetID = $( event.target ).data( 'toggle-target' );

		if ( targetID ) {
			$( targetID ).toggle();
		}
	}

	/**
	 * Toggles the element with the ID given in the according toggler's data attribute if the toggler is checked.
	 * @param {Event} event - The change event of an input element.
	 */
	toggleElementIfChecked( event ) {
		const $toggler = event.data.$toggler;
		const targetID = $toggler.data( 'toggle-target' );

		if ( targetID ) {
			$( targetID ).toggle( $toggler.is( ':checked' ) );
		}
	}
}
