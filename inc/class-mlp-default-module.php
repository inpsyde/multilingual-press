<?php
/**
 * Module Name: Multilingual Press Default Module
 * Description: This module contains the basic UI and userinput handling of the free version of MlP
 * Author:      Inpsyde GmbH
 * Version:     0.1a
 * Author URI:  http://inpsyde.com
 */

//@TODO: should this class extend the settings page class?

if ( !class_exists( 'inpsyde_multilingualpress_default_module' ) ) {

    class inpsyde_multilingualpress_default_module extends Inpsyde_Multilingualpress {

        static protected $class_object = NULL; // static class object variable
        
        /**
         * Localization var
         * 
         * @var string $mlp
         */
        static private $mlp = FALSE; // 

        /**
         * Load the object and get the current state 
         *
         * @access public
         * @since 0.1
         * @return $class_object
         */
        function get_object() {

            if ( NULL == self::$class_object ) {
                self::$class_object = new self;
            }
            return self::$class_object;
        }

        /**
         * init function to register all used hooks and set the Database Table 
         *
         * @access public
         * @since 0.1
         * @uses add_action, get_site_option
         * @return void
         */
        function __construct() {
                       
            // Get the Multilingual Press textdomain
            add_action( 'init', array( $this, 'get_mlp_textdomain' ) );
            
//            // Use this hook to add a meta box to the networks options page
//            add_action( 'mlp_options_page_add_metabox', array( $this, 'add_metabox' ), 1 );
//            
//            // Use this hook to handle the user input of your modules' options page form fields
//            add_action( 'mlp_settings_save_fields', array( $this, 'save_options_page_form_fields' ) );
            
            // Use this hook to add form fields to the blog options page
            add_action( 'mlp_blogs_add_fields', array( $this, 'draw_blog_settings_form_fields' ), 1 );
            
            // Use this hook to handle the user input of your modules' blog settings form fields
            add_action( 'mlp_blogs_save_fields', array( $this, 'save_blog_settings_form_fields' ) );
        }
        
        /**
         * Get the MlP textdomain. 
         * Usage: 
         * _e( 'Your text', $this->mlp );
         * __( 'Your text', $this->mlp );
         * etc.
         * 
         */
        public function get_mlp_textdomain() {
            
            $this->mlp = parent::get_textdomain();
        }
               
        /**
         * Display the default form fields
         * 
         * @param   type $current_blog_id | The ID of the current blog
         * @return  type 
         * @since   0.5.5b
         */
        public function draw_blog_settings_form_fields( $current_blog_id ) {
            
            $lang_codes = parent::$class_object->lang_codes;

            // get registered blogs
            $siteoption = get_site_option( 'inpsyde_multilingual' );

            // Get values to display
            // in form fields
            $lang_title = ISSET( $siteoption[ $current_blog_id ][ 'text' ] ) ? stripslashes( $siteoption[ $current_blog_id ][ 'text' ] ) : '';
            $selected = ISSET( $siteoption[ $current_blog_id ][ 'lang' ] ) ? $siteoption[ $current_blog_id ][ 'lang' ] : '';
            $blogoption_flag = esc_url( get_blog_option( $current_blog_id, 'inpsyde_multilingual_flag_url' ) );
            ?>

            <!-- Language select, alt. language title and flag image url -->

            <div class="postbox">
                <div title="Click to toggle" class="handlediv"><br></div>
                <h3 class="hndle"><?php _e( 'Language', $this->get_textdomain() ); ?></h3>
                <div class="inside">

                    <table class="form-table" id="mlp_blog_language">
                        <tr class="form-field">
                            <th><?php _e( 'Choose blog language', $this->get_textdomain() ) ?></th>
                            <td>
                                <?php
                                if ( !empty( $lang_codes ) ) {
                                    ?>
                                    <select name="inpsyde_multilingual_lang" id="inpsyde_multilingual_lang">
                                        <option value="-1"><?php _e( 'choose language', $this->get_textdomain() ); ?></option>
                                        <option  style="background-position:0px 50%;background-image:url(<?php echo plugins_url( 'flags/us.gif', __FILE__ ); ?>);background-repeat:no-repeat;padding-left:30px;" value="en_US" <?php echo selected( 'en_US', $selected ); ?>><?php _e( 'English (US)', $this->get_textdomain() ) ?></option>

                                        <?php
                                        
                                        foreach ( $lang_codes AS $language_code => $language_name ) :

                                            if ( 5 == strlen( $language_code ) ) {

                                                $language_code_flag = strtolower( substr( $language_code, 3, 2 ) );
                                            }
                                            else
                                                $language_code_flag = $language_code;

                                            if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . '/flags/' . $language_code_flag . '.gif' ) ) :
                                                ?>

                                                <option style="background-position:0px 50%; background-image:url(<?php echo plugins_url( 'flags/' . $language_code_flag . '.gif', dirname( __FILE__ ) ); ?>); background-repeat:no-repeat; padding-left:30px;" value="<?php echo $language_code ?>"<?php echo selected( $selected, $language_code, false ); ?>><?php echo esc_html( $language_name ); ?></option>

                                                <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </select>
                                    <br />

                                    <span class="description"><?php _e( 'Determine blog language and flag. This will be used in the frontend widget.', $this->get_textdomain() ); ?></span>

                                <?php } ?>
                            </td>
                        </tr>
                        <tr id="mlp_check_language"><th></th><td></td></tr>
                        <tr>
                            <th>
                                <?php _e( 'Alternative language title', $this->get_textdomain() ); ?>
                            </th>
                            <td>
                                <input class="regular-text" type="text" id="inpsyde_multilingual_text" name="inpsyde_multilingual_text" value="<?php echo $lang_title; ?>" />
                                <br />
                                <span class="description"><?php _e( 'Enter a title here that you want to be displayed in the frontend instead of the default one (i.e. "My English Site")' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'Blog flag image URL', $this->get_textdomain() ); ?>
                            </th>
                            <td>
                                <input class="regular-text" type="text" id="inpsyde_multilingual_flag_url" name="inpsyde_multilingual_flag_url" value="<?php echo $blogoption_flag; ?>" />
                                <br />
                                <span class="description"><?php _e( '(Optional, must begin with http://)', $this->get_textdomain() ); ?></span>
                            </td>
                        </tr>
                    </table>

                </div>
            </div>

            <?php
            // Only display this part if there are blogs to interlink
            if ( 1 >= count( mlp_get_available_languages( FALSE ) ) )
                return;
            ?>


            <!-- Blog relationships -->		

            <div class="postbox">
                <div title="Click to toggle" class="handlediv"><br></div>
                <h3 class="hndle"><?php _e( 'Blog Relationships', $this->get_textdomain() ); ?></h3>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th><?php _e( 'Multilingual blog relationships', $this->get_textdomain() ) ?></th>
                            <td>
            <?php
            foreach ( $siteoption as $blog_id => $meta ) {

                // Filter out current blog
                if ( $current_blog_id === $blog_id )
                    continue;

                // Get blog display name
                switch_to_blog( $blog_id );
                $blog_name = get_bloginfo( 'name' );
                restore_current_blog();

                // Get current settings
                $related_blogs = get_blog_option( $current_blog_id, 'inpsyde_multilingual_blog_relationship' );
                if ( is_array( $related_blogs ) && in_array( $blog_id, $related_blogs ) )
                    $selected = 'checked="checked"';
                else
                    $selected = '';
                ?>
                                    <input id="related_blog_<?php echo $blog_id; ?>" <?php echo $selected; ?> type="checkbox" name="related_blogs[]" value="<?php echo $blog_id ?>" /> <?php echo $blog_name; ?> - <?php echo $meta[ 'lang' ] ?> - <?php echo $meta[ 'text' ] ?><br />
                                    <?php
                                }
                                ?>
                                <span class="description"><?php _e( 'Posts and pages will be automatically duplicated into these blogs', $this->get_textdomain() ); ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php
        }
        
        /**
         * Process the default form fields
         *  
         * @param   array $data | User input
         * @since   0.5.5b
         */
        public function save_blog_settings_form_fields( $data ) {

            $current_blog_id = intval( $data[ 'id' ] );

            // Language and descriptions
            $siteoption = get_site_option( 'inpsyde_multilingual' );
            unset( $siteoption[ $current_blog_id ] );
            if ( '' != $data[ 'inpsyde_multilingual_lang' ] || '' != $data[ 'inpsyde_multilingual' ] ) {

                if ( !is_array( $siteoption ) )
                    $siteoption = array( );

                $siteoption[ $current_blog_id ] = array( 'text' => esc_attr( $data[ 'inpsyde_multilingual_text' ] ), 'lang' => esc_attr( $data[ 'inpsyde_multilingual_lang' ] ) );
            }
            update_site_option( 'inpsyde_multilingual', $siteoption );

            // Custom flag URL
            if ( ISSET( $data[ 'inpsyde_multilingual_flag_url' ] ) )
                update_blog_option( $current_blog_id, 'inpsyde_multilingual_flag_url', esc_url( $data[ 'inpsyde_multilingual_flag_url' ], array( 'http' ) ) );

            // Update blog relationships
            // Get blogs related to the current blog
            $all_blogs = get_site_option( 'inpsyde_multilingual' );

            if ( !$all_blogs )
                $all_blogs = array( );

            // The user defined new relationships for this blog. We add it's own ID 
            // for internal purposes
            $data[ 'related_blogs' ][ ] = $current_blog_id;
            $new_rel = $data[ 'related_blogs' ];

            //@TODO: unchanged settings?
            // Loop through related blogs
            foreach ( $all_blogs as $blog_id => $blog_data ) {

                if ( $current_blog_id == $blog_id )
                    continue;

                // 1. Get related blogs' current relationships 
                $current_rel = get_blog_option( $blog_id, 'inpsyde_multilingual_blog_relationship' );

                if ( !is_array( $current_rel ) )
                    $current_rel = array( );

                // 2. Compare old to new relationships
                // Get the key of the current blog in the relationships array of the looped blog
                $key = array_search( $current_blog_id, $current_rel );

                if ( in_array( $blog_id, $new_rel ) ) {

                    // Connect these blogs, if not already.
                    if ( FALSE === $key ) {
                        $current_rel[ ] = $current_blog_id;
                    }
                } else {
                    // These blogs should not be connected. Delete
                    // possibly existing connection
                    if ( FALSE !== $key && ISSET( $current_rel[ $key ] ) )
                        unset( $current_rel[ $key ] );
                }

                // $current_rel should be our relationships array for the currently looped blog
                update_blog_option( $blog_id, 'inpsyde_multilingual_blog_relationship', $current_rel );
            }

            // Save Blog-Relationship
            // @TODO: validate user input
            update_blog_option( $current_blog_id, 'inpsyde_multilingual_blog_relationship', $new_rel );

            // Do not pass on these values 
//			$unset = array( 'inpsyde_multilingual', 'inpsyde_multilingual_text', 'inpsyde_multilingual_lang', 'inpsyde_multilingual_flag_url', 'related_blogs' );
//			foreach ( $unset AS $del ) {
//				unset( $data[ $del ] );
//			}
        }

        /**
         * Add meta box
         * 
         */
        public function add_metabox() {

            add_meta_box( 'demo_metabox', __( 'Demo Module Metabox', $this->mlp ), array( $this, 'draw_options_page_form_fields' ), inpsyde_multilingualpress_settingspage::$class_object->options_page, 'normal', 'low', TRUE );
        }
        
        /**
         * This is the callback of the metabox
         * used to display the modules options page
         * form fields
         * 
         */
        public function draw_options_page_form_fields() {
            
            $options = get_site_option( 'inpsyde_multilingual_default_module-module' );
            ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
            <?php _e( 'Example 1', $this->mlp ); ?>
                        </th>
                        <td>
                            <input type="checkbox" <?php echo ( ( TRUE == $options[ 'mlp_default_module1' ] ) ? 'checked="checked"' : '' ); ?> id="mlp_default_module" value="true" name="mlp_default_module1" />

                        </td>
                    </tr>
                    <tr>
                        <th>
            <?php _e( 'Example 2', $this->mlp ); ?>
                        </th>
                        <td>
                            <input type="checkbox" <?php echo ( ( TRUE == $options[ 'mlp_default_module2' ] ) ? 'checked="checked"' : '' ); ?> id="mlp_default_module" value="true" name="mlp_default_module2" />

                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }

        /**
         * Hook into mlp_settings_save_fields to 
         * handle module user input
         * 
         */        
        public function save_options_page_form_fields() {

            // Get current site options
            $options = get_site_option( 'inpsyde_multilingual_default_module-module' );
            
            // Get values from submitted form
            $options[ 'mlp_default_module1' ] = ( ISSET( $_POST[ 'mlp_default_module1' ] ) ) ? TRUE : FALSE;
            $options[ 'mlp_default_module2' ] = ( ISSET( $_POST[ 'mlp_default_module2' ] ) ) ? TRUE : FALSE;

            update_site_option( 'inpsyde_multilingual_default_module-module', $options );
        }

    }

    inpsyde_multilingualpress_default_module::get_object();
}
?>