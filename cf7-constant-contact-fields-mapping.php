<?php

/*
Plugin Name: CF7 Constant Contact Fields Mapping 
Plugin URL: http://reloadweb.co.uk
Description: Mapping contact form 7 fields (tags) with constant contact fields, which input field of the contact form corresponds to which property of Constant Contact’s contact data.
Version: 1.0.0
Author: Reload Web (Ahmed)
Author URI: http://reloadweb.co.uk
Text Domain: cf7-cc-fields-mapping
Domain Path: /languages
*/

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( function_exists( 'cfccfm_fs' ) ) {
    cfccfm_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'cfccfm_fs' ) ) {
        // Create a helper function for easy SDK access.
        function cfccfm_fs()
        {
            global  $cfccfm_fs ;
            
            if ( !isset( $cfccfm_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $cfccfm_fs = fs_dynamic_init( array(
                    'id'             => '5791',
                    'slug'           => 'cf7-constant-contact-fields-mapping',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_8b18f23dfa30c111016ca6f619350',
                    'is_premium'     => false,
                    'premium_suffix' => 'Custom Fields Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                    'slug'    => 'cf7-cc-fields-mapping-guide',
                    'support' => false,
                    'parent'  => array(
                    'slug' => 'wpcf7',
                ),
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $cfccfm_fs;
        }
        
        // Init Freemius.
        cfccfm_fs();
        // Signal that SDK was initiated.
        do_action( 'cfccfm_fs_loaded' );
    }
    
    define( 'CF7_CC_FIELDS_MAPPING_VERSION', '1.0.0' );
    define( 'CF7_CC_FIELDS_MAPPING_DB_VERSION', '1.0.0' );
    define( 'CF7_CC_FIELDS_MAPPING_ROOT', dirname( __FILE__ ) );
    define( 'CF7_CC_FIELDS_MAPPING_URL', plugins_url( '/', __FILE__ ) );
    define( 'CF7_CC_FIELDS_MAPPING_BASE_FILE', basename( dirname( __FILE__ ) ) . '/cf7-constant-contact-fields-mapping' );
    define( 'CF7_CC_FIELDS_MAPPING_BASE_NAME', plugin_basename( __FILE__ ) );
    define( 'CF7_CC_FIELDS_MAPPING_PATH', plugin_dir_path( __FILE__ ) );
    //use for include files to other files
    define( 'CF7_CC_FIELDS_MAPPING_PRODUCT_NAME', 'CF7 Constant Contact Fields Mapping' );
    define( 'CF7_CC_FIELDS_MAPPING_CURRENT_THEME', get_stylesheet_directory() );
    /*
     * include ConstantContact post request classes
     */
    if ( !class_exists( 'Cf7_CC_Fields_Mapping_ConstantContact_ContactPostRequest' ) ) {
        include CF7_CC_FIELDS_MAPPING_ROOT . '/includes/class-cf7-cc-fields-mapping.php';
    }
    global  $ccfields ;
    function cf7_cc_fields_mapping_cc_get_all_fields()
    {
        $plugin = plugin_basename( __FILE__ );
        
        if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) || !file_exists( plugin_dir_path( __DIR__ ) . 'contact-form-7/wp-contact-form-7.php' ) ) {
            add_action( 'admin_notices', 'contact_form_7_not_activated' );
            deactivate_plugins( $plugin );
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
        } else {
            global  $ccfields ;
            $ccfields['first_name'] = __( 'First Name', 'cf7-cc-fields-mapping' );
            $ccfields['last_name'] = __( 'Last Name', 'cf7-cc-fields-mapping' );
            $ccfields['first_name_last_name'] = __( 'Full Name (First and Last)', 'cf7-cc-fields-mapping' );
            $ccfields['email_address'] = __( 'Email Address', 'cf7-cc-fields-mapping' );
            $ccfields['job_title'] = __( 'Job Title', 'cf7-cc-fields-mapping' );
            $ccfields['company_name'] = __( 'Company Name', 'cf7-cc-fields-mapping' );
            $ccfields['birthday_month'] = __( 'Birthday Month', 'cf7-cc-fields-mapping' );
            $ccfields['birthday_day'] = __( 'Birthday Day', 'cf7-cc-fields-mapping' );
            $ccfields['birthday_month_day'] = __( 'Birthday Month & Day', 'cf7-cc-fields-mapping' );
            $ccfields['anniversary'] = __( 'Anniversary', 'cf7-cc-fields-mapping' );
            $ccfields['phone_numbers'] = __( 'Phone Number', 'cf7-cc-fields-mapping' );
            $ccfields['street'] = __( 'Address Street', 'cf7-cc-fields-mapping' );
            $ccfields['city'] = __( 'Address City', 'cf7-cc-fields-mapping' );
            $ccfields['state'] = __( 'Address State', 'cf7-cc-fields-mapping' );
            $ccfields['postal_code'] = __( 'Address Postal Code', 'cf7-cc-fields-mapping' );
            $ccfields['country'] = __( 'Address Country', 'cf7-cc-fields-mapping' );
            $ccfields = apply_filters( 'cf7_cc_fields_array', $ccfields );
        }
    
    }
    
    //  is contact form 7 plugin exist add constant contact fields
    add_action( 'admin_init', 'cf7_cc_fields_mapping_cc_get_all_fields' );
    register_uninstall_hook( __FILE__, 'cf7_cc_fields_mapping_uninstall' );
    function cf7_cc_fields_mapping_uninstall()
    {
        delete_post_meta_by_key( 'cf7_cc_fields_mapping_settings' );
        WPCF7::update_option( 'constant_contact_custom_fields', '' );
    }
    
    function cf7_cc_fields_mapping_save_fields( $post )
    {
        $map_fields = array();
        $form_fields = ( isset( $_POST['form_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['form_fields'] ) ) : '' );
        $cc_fields = ( isset( $_POST['cc_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['cc_fields'] ) ) : '' );
        for ( $id = 0 ;  $id < count( $form_fields ) ;  $id++ ) {
            $map_fields[$cc_fields[$id]] = $form_fields[$id];
        }
        update_post_meta( $post->id(), 'cf7_cc_fields_mapping_settings', $map_fields );
    }
    
    add_action( 'wpcf7_after_save', 'cf7_cc_fields_mapping_save_fields' );
    //Display admin notices
    function contact_form_7_not_activated()
    {
        ?>
				<div class="notice notice-warning is-dismissible">
					<p><?php 
        _e( 'Contact Form 7 Constant Contact Fields Mapping - requires Contact Form 7 plugin to be installed and activated.', 'cf7-cc-fields-mapping' );
        ?></p>
				</div>
	<?php 
    }
    
    load_plugin_textdomain( 'cf7-cc-fields-mapping', false, basename( dirname( __FILE__ ) ) . '/languages' );
    function cf7_cc_fields_mapping_enqueue_select2_jquery( $hook )
    {
        
        if ( $hook == 'toplevel_page_wpcf7' ) {
            wp_register_style(
                'select2css',
                CF7_CC_FIELDS_MAPPING_URL . '/assets/select2.min.css',
                false,
                '4.0.13',
                'all'
            );
            wp_register_script(
                'select2',
                CF7_CC_FIELDS_MAPPING_URL . '/assets/select2.min.js',
                array( 'jquery' ),
                '4.0.13',
                true
            );
            wp_enqueue_style( 'select2css' );
            wp_enqueue_script( 'select2' );
        }
    
    }
    
    add_action( 'admin_enqueue_scripts', 'cf7_cc_fields_mapping_enqueue_select2_jquery' );
    function cf7_cc_fields_mapping_menu_page()
    {
        if ( current_user_can( 'wpcf7_edit_contact_forms' ) ) {
            add_submenu_page(
                'wpcf7',
                __( 'Constant Contact Fields Mapping', 'cf7-cc-fields-mapping' ),
                __( 'Constant Contact Fields Mapping', 'cf7-cc-fields-mapping' ),
                'manage_options',
                'cf7-cc-fields-mapping-guide',
                'cf7_cc_fields_mapping_guide'
            );
        }
    }
    
    function cf7_cc_fields_mapping_guide()
    {
        echo  "<h3> Fields Mapping </h3>" ;
        echo  '<p>' . sprintf( esc_html( __( 'Step 1: Connect the Constant Contact API if you are not connected, please %s to connect the Constant Contact API follow instruction guide.', 'cf7-cc-fields-mapping' ) ), wpcf7_link( admin_url( 'admin.php?page=wpcf7-integration' ), __( 'click here', 'cf7-cc-fields-mapping' ) ) ) . '</p>' ;
        echo  '<p>Step 2: Create or Edit the Contact Form 7 form from which you want to map form field data with constant contact field. Set up the form in the Form and Mail tabs and hit "Save". Then, go to the new "Constant Contact Fields Mapping" tab. </p>' ;
        echo  'Step 3:  On the "Constant Contact Fields Mapping" tab,  select fields which you want to map select form field (tag) from form field dropdwon then select constact contact field from constant contact dropdown and hit "Save".  Now form field (tag) are mapped wtih constact contact field which you are selected in from specific dropdown.' ;
        echo  "<h3>Custom Fields Mapping </h3>" ;
        echo  '<p>' . sprintf( esc_html( __( 'Step 1: You need to %s vesion of plugin, after buy you will receive confirmation email with plugin installation guide please flollow installation guide', 'cf7-cc-fields-mapping' ) ), wpcf7_link( cfccfm_fs()->get_upgrade_url(), __( 'Buy Pro', 'cf7-cc-fields-mapping' ) ) ) . '</p>' ;
        echo  '<p>' . sprintf( esc_html( __( 'Step 2: Login to your constant contact account %s After adding custom fields in constant contact account.', 'cf7-cc-fields-mapping' ) ), wpcf7_link( __( 'https://knowledgebase.constantcontact.com/articles/KnowledgeBase/5328-add-and-manage-custom-fields?lang=en_US', 'cf7-cc-fields-mapping' ), __( 'Add, View, and Manage Custom Fields.', 'cf7-cc-fields-mapping' ) ) ) . '</p>' ;
        echo  '<p>Step 3: Create or Edit the Contact Form 7 form from which you want to map form field data with constant contact field. Set up the form in the Form and Mail tabs and hit "Save". Then, go to the new "Constant Contact Fields Mapping" tab. </p>' ;
        echo  'Step 4: Now you can see constant contact fields dropdown is populated with custom fields On the "Constant Contact Fields Mapping" tab,  select fields which you want to map select form field (tag) from form field dropdwon then select constact contact field from constant contact dropdown and hit "Save".  Now form field (tag) are mapped wtih constact contact field which you are selected in from specific dropdown.' ;
    }
    
    // register admin menu
    add_action( 'admin_menu', 'cf7_cc_fields_mapping_menu_page' );
    function cf7_cc_fields_mapping_select2jquery_inline()
    {
        ?>
	 <style>
	  .cc-fields-mapping .select2-search__field {
	width:180px !important;
	}
	 </style>
	<script type='text/javascript'>
	jQuery(document).ready(function ($) {
		
		 $('.cf7-form-field-multiple').select2({
					  placeholder: '<?php 
        echo  esc_html( __( 'Select form fields', 'cf7-cc-fields-mapping' ) ) ;
        ?>'
				 });
		$('.cf7-form-ccfield-multiple').select2({
					  placeholder: '<?php 
        echo  esc_html( __( 'Select constant contact fields', 'cf7-cc-fields-mapping' ) ) ;
        ?>'
				 });
		$("select").on("select2:select", function (evt) {
		  var element = evt.params.data.element;
		  var $element = $(element);
		  $element.detach();
		  $(this).append($element);
		  $(this).trigger("change");
		});
		
	});
	</script>
		<?php 
    }
    
    add_action( 'admin_footer-toplevel_page_wpcf7', 'cf7_cc_fields_mapping_select2jquery_inline' );
    // Add new tab to contact form 7 editors panel
    add_filter( 'wpcf7_editor_panels', 'cf7_cc_fields_mapping_editor_panels' );
    /**
     * Add new tab to contact form 7 editors panel
     * @since 1.0
     */
    function cf7_cc_fields_mapping_editor_panels( $panels )
    {
        if ( current_user_can( 'wpcf7_edit_contact_form' ) ) {
            $panels['cf7_cc_fields_mapping'] = array(
                'title'    => __( 'Constant Contact Fields Mapping', 'cf7-cc-fields-mapping' ),
                'callback' => 'cf7_contant_contact_field_mapping_editor_panel',
            );
        }
        return $panels;
    }
    
    /*
     * fields mapping settings editors panel  
     * @since 1.0
     */
    function cf7_contant_contact_field_mapping_editor_panel( $post )
    {
        global  $ccfields ;
        $form_id = $post->id();
        $service = WPCF7_ConstantContact::get_instance();
        
        if ( !$service->is_active() ) {
            echo  '<p>' . sprintf( esc_html( __( 'This site is not connected to the Constant Contact API. Please %s to connect the Constant Contact API.', 'cf7-cc-fields-mapping' ) ), wpcf7_link( admin_url( 'admin.php?page=wpcf7-integration' ), __( 'click here', 'cf7-cc-fields-mapping' ) ) ) . '</p>' ;
        } else {
            $fields_data = get_post_meta( $form_id, 'cf7_cc_fields_mapping_settings', true );
            $tags = $post->scan_form_tags();
            foreach ( (array) $tags as $tag ) {
                if ( empty($tag->name) ) {
                    continue;
                }
                $mailtags[] = $tag->name;
            }
            ?>
		  <form method="post">
			  <div class="cc-fields-mapping">
				<h2><span><?php 
            echo  esc_html( __( 'Fields Mapping', 'cf7-cc-fields-mapping' ) ) ;
            ?></span> 
				<?php 
            ?>
			   <span style="font-size:13px;" class="cc-fields-mapping-info">
			   <?php 
            echo  sprintf( esc_html( __( '( Map constant contact custom fields %s vesion of the plugin.)', 'cf7-cc-fields-mapping' ) ), wpcf7_link( cfccfm_fs()->get_upgrade_url(), __( 'Buy Pro', 'cf7-cc-fields-mapping' ) ) ) ;
            ?>
               </span>
				<?php 
            ?>
				</h2>
				 <p>
				   <label  for="id_label_form_fields" style="width:160px; font-weight:bold; display:inline-block;"><?php 
            echo  esc_html( __( 'Form Fields', 'cf7-cc-fields-mapping' ) ) ;
            ?></label>
						<select class="cf7-form-field-multiple" name="form_fields[]" id="id_label_form_fields" multiple="multiple" style="width: 75%">
						<optgroup label="<?php 
            echo  esc_html( __( 'Fields', 'cf7-cc-fields-mapping' ) ) ;
            ?>">
						<?php 
            foreach ( (array) $mailtags as $tag ) {
                ?>
						<option value="<?php 
                echo  $tag ;
                ?>" <?php 
                if ( in_array( $tag, $fields_data ) ) {
                    ?> selected="selected"<?php 
                }
                ?>><?php 
                echo  $tag ;
                ?></option>
						<?php 
            }
            ?>
						</optgroup>
						</select>
				  
				</p>
				
				<p style="text-align:center;"><img style="width:36px;" src="<?php 
            echo  CF7_CC_FIELDS_MAPPING_URL ;
            ?>/images/map.png" /></p>
				
				 <p>
				   <label  for="id_label_form_cc_fields" style="width:160px;  font-weight:bold; display:inline-block;"><?php 
            echo  esc_html( __( 'Constant Contact Fields', 'cf7-cc-fields-mapping' ) ) ;
            ?></label>
						<select class="cf7-form-ccfield-multiple" name="cc_fields[]" id="id_label_form_cc_fields" multiple="multiple" style="width: 75%">
						<optgroup label="<?php 
            echo  esc_html( __( 'Default Fields', 'cf7-cc-fields-mapping' ) ) ;
            ?>">
						<?php 
            foreach ( (array) $ccfields as $key => $value ) {
                ?>
						<option value="<?php 
                echo  $key ;
                ?>" <?php 
                if ( array_key_exists( $key, $fields_data ) ) {
                    ?> selected="selected"<?php 
                }
                ?>><?php 
                echo  $value ;
                ?></option>
						<?php 
            }
            ?>
						</optgroup>
						<?php 
            ?>
						<optgroup label="<?php 
            echo  esc_html( __( 'Custom Fields', 'cf7-cc-fields-mapping' ) ) ;
            ?>">
						 <option value=""><?php 
            echo  esc_html( __( 'Buy Pro', 'cf7-cc-fields-mapping' ) ) ;
            ?></option>
						</optgroup>
						<?php 
            ?>
						</select>
				  
				</p>
				
			 </div> 
		  </form>
		  <?php 
        }
    
    }
    
    function cf7_cc_fields_mapping_add_ConstantContact_ContactPostRequest( $ContactPostRequest )
    {
        return 'Cf7_CC_Fields_Mapping_ConstantContact_ContactPostRequest';
    }
    
    add_filter(
        'wpcf7_constant_contact_contact_post_request_builder',
        'cf7_cc_fields_mapping_add_ConstantContact_ContactPostRequest',
        10,
        1
    );
}
