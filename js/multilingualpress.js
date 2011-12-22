jQuery.noConflict();
( function( $ ) {
    
    multilingualpress = {
        init : function () {
            
            this.draw_tab();
            this.bind_ajax_tab_handler();
            this.bind_submit_setting_form();
            this.form_fields_expandable();
        },
    	
        bind_submit_setting_form : function() {
            
            $( '#multilingualpress_settings' ).live( 'submit', function() {
                multilingualpress.submit_form();
                return false;
            } );
        },
    	
        submit_form : function() {
   
            var related_blogs = $.map( $( 'input[id=related_blog]' ), function(e) {
                if( $( e ).is(':checked') ) { return $( e ).val(); }
            } );
    		
            var multilang_post_data = {
                action: 'save_multilang_settings',
                inpsyde_multilingual_text: $( '#inpsyde_multilingual_text' ).val(),
                inpsyde_multilingual_lang: $( '#inpsyde_multilingual_lang' ).val(),
                inpsyde_multilingual_flag_url: $( '#inpsyde_multilingual_flag_url' ).val(),
                related_blogs: related_blogs,
                id: mlp_loc.blog_id,
                form_nonce: mlp_loc.ajax_form_nonce
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
    	
        draw_tab: function() {
            
            $( 'h3.nav-tab-wrapper:first' ).append( '<a id="mlp_settings_tab" class="nav-tab" href="#">' + mlp_loc.tab_label + '</a>' );
        },
        
        bind_ajax_tab_handler : function() {
            
            $( 'a#mlp_settings_tab' ).live( 'click', function() {
                
                // Set tab active
                $( 'h3.nav-tab-wrapper:first a' ).each( function() {
                    $( this ).removeClass( 'nav-tab-active' );
                });
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
                });
            });
        },
        
        form_fields_expandable : function() {
            
            $( 'form#multilingualpress_settings .handlediv' ).live( 'click', function() {
                
                $( this ).parent( '.postbox' ).toggleClass( 'closed' );
                   
            });
   
        }
        
    };
    $( document ).ready( function( $ ) {
        multilingualpress.init();
    } );
} )( jQuery );