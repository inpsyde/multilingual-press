/**
 * jQuery Library for Multilingual Press
 * 
 * @author		fb, rw, ms, th
 * @version		0.8
 * @package		mlp
 * @subpackage	jquery lib
 * 
 * @todo		Doc
 */

jQuery.noConflict();
( function( $ ) {
	
	/**
	 * Class Holder
	 */
	multilingual_press = {
		
		/**
		 * Initial Function
		 */
		init : function () {
			this.do_blog_checkup();
			this.draw_tab();
			this.bind_ajax_tab_handler();
			this.bind_submit_setting_form();
			this.form_fields_expandable();
		},
		
		/**
		 * 
		 */
		bind_submit_setting_form : function() {
			
			$( '#multilingualpress_settings' ).live( 'submit', function() {
				// Serialize form field/data
				var serialized_data = $( this ).serialize();
				multilingual_press.submit_form( serialized_data );
				return false;
			} );
		},
		
		/**
		 * 
		 * @param serialized_data
		 */
		submit_form : function( serialized_data ) {
			
			var multilang_post_data = {
				action: 'save_multilang_settings',
				serialized_data: serialized_data + '&id=' + mlp_loc.blog_id,
				form_nonce: mlp_loc.ajax_form_nonce,
				id: mlp_loc.blog_id
			};
			
			var multilang_saved_settings = $.ajax( {
				url: ajaxurl,
				data: multilang_post_data,
				async: false,
				type: 'POST',
				success: function ( response ) {
					return response;
				}
			} ).responseText;
			
			$( '#multilingualpress_settings' ).before( multilang_saved_settings );
		},
		
		/**
		 * 
		 */
		draw_tab: function() {
			$( 'h3.nav-tab-wrapper:first' ).append( '<a id="mlp_settings_tab" class="nav-tab" href="#">' + mlp_loc.tab_label + '</a>' );
		},
		
		/**
		 * 
		 */
		bind_ajax_tab_handler : function() {
			
			$( 'a#mlp_settings_tab' ).live( 'click', function() {
				
				// Set tab active
				$( 'h3.nav-tab-wrapper:first a' ).each( function() {
					$( this ).removeClass( 'nav-tab-active' );
				} );
				$( this ).addClass( 'nav-tab-active' );
								
				var data = {
					action: 'tab_form',
					id: mlp_loc.blog_id,
					tab_nonce: mlp_loc.ajax_tab_nonce
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.post( ajaxurl, data, function( response ) {
					// Replace content
					$( '.wrap:first' ).html( response );
				} );
			} );
		},
		
		/**
		 * 
		 */
		form_fields_expandable : function() {
			$( 'form#multilingualpress_settings .handlediv' ).live( 'click', function() {
				$( this ).parent( '.postbox' ).toggleClass( 'closed' );
			} );
		},
		
		/**
		 * Retrieves all responses from the blog checkup
		 * 
		 * @since	0.8
		 */
		do_blog_checkup : function() {
			if ( ! '#multilingual_press_checkup' )
				return;
			
			$( '#multilingual_press_checkup_link' ).live( 'click', function() {
				var data = {
					action: 'checkup_blogs'
				};
				
				$.ajax( {
    				url: ajaxurl,
    				data: data,
    				async: true,
    				success: function ( response ) {
    					$( '#multilingual_press_checkup' ).append( response ).delay( 250 ).animate( {
    						backgroundColor: 'lightYellow',
    						borderBottomColor: '#E6DB55', 
    						borderLeftColor: '#E6DB55',
    						borderRightColor: '#E6DB55',
    						borderTopColor: '#E6DB55'
    					}, 500 ).delay( 3500 ).slideUp();
    				}
    			} );
			} );
		}
	};
	
	$( document ).ready( function( $ ) {
		multilingual_press.init();
	} );
	
} )( jQuery );