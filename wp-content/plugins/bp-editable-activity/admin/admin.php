<?php
//we use options buddy
require_once dirname( __FILE__ ) . '/class.options-buddy.php';


class BP_Editable_Activity_Admin {
 
    private $page;
    
    public function __construct() {
        //create a options page
        //make sure to read the code below
        $this->page = new OptionsBuddy_Settings_Page( 'bp-editable-activity' );
        $this->page->set_bp_mode();//make it to use bp_get_option/bp_update_option
        //by default,  example_page will be used as option name and you can retrieve all options by using get_option('example_page')
        //if you want use a different option_name, you can pass it to set_option_name method as below
        
        //$this->setting_page_example->set_option_name('my_new_option_name');
        //now all the options for example_page will be stored in the 'my_new_option_name' option and you can get it by using get_option('my_new_option_name')
        
        //if you don't want to group all the fields in single option and want to store each field individually in the option table, you can set that too as below
        // if you cann use_unique_option method, all the fields will be stored in individual option(the option name will be field name ) and 
        //you can retrieve them using get_option('field_name')
        
       // $this->setting_page_example->use_unique_option();
        
        //incase your mood changed and you want to use single option to store evrything, you can call this use_single_option method again
        //use single option is the default 
        //$this->setting_page_example->use_single_option();
        
        
        //if it pleases you, you can set the optgroup too, if you don't set,. it is same as the page name
        //$this->setting_page_example->set_optgroup('buddypress');
        //now, let us create an options page, what do you say
        
        add_action( 'admin_init', array($this, 'admin_init'));
        add_action( 'admin_menu', array($this, 'admin_menu') );
        add_action( 'admin_footer', array($this, 'admin_css') );
    }

    function admin_init() {

        //set the settings
        
        $page = $this->page;
        //add_section
        //you can pass section_id, section_title, section_description, the section id must be unique for this page, section descriptiopn is optional
        $page->add_section('basic_section', __( 'BP Editable Activity Settings' ), __('You can manage all the settings for BP editable activity from here.'));
        
        
        //now, if we want, we can fetch a section and add some fields to it
        //I am not feeling adventurous, so I will simpley copy the example from Tareq's code
        //link https://github.com/tareq1988/wordpress-settings-api-class/blob/master/settings-api.php#L68
        //and use here
        // 
        //add fields
        $page->get_section('basic_section')->add_fields(array( //remember, we registered basic section earlier
                array(
                    'name' => 'allow_activity_editing',
                    'label' => __( 'Allow editing Activity?' ),//you already know it from previous example
                    'desc' => __( 'Do you want your Users to be able to edit activity?' ),// this is used as the description of the field
                    'type'=>'radio',
                    'default'=> 'yes',
                    'options' => array(
                        'yes' => 'Yes',//key=>label
                        'no' => 'No'
                    )
                ),
                array(
                    'name' => 'activity_allowed_time',
                    'label' => 'Allow editing only before the given time',
                    'desc' => __( 'Put a number. This is the number of minute after posting of activity. Zero means no limit' ),
                    'type' => 'text',
                    'default'=> 10 //10 minutes
                ),
                array(
                    'name' => 'keep_log',
                    'label' => __( 'Keep Log of Edited activity Items?' ),
                    'desc' => __( 'If you enable, It will store the original activity content in activity meta table. Not suggested if you don\'t plan to use it in future!' ),
                    'type' => 'radio',
                    'default'=> 'yes',
                    'options' => array(
                        'yes' => 'Yes',
                        'no'  => 'No'  
                    ),
                    
                ),
            array(
                'name'  => 'allow_comment_editing',
                'label' => __( 'Allow Editing of activity Comment', 'bp-editable-activity' ),
                'desc'  => __( 'Do you want your users to be able to edit their activity comment?' ),
                'type'  => 'radio',
                'default' => 'yes',
                'options' => array(
                        'yes'   => __( 'Yes', 'bp-editable-activity'),
                        'no'    => __( 'No', 'bp-editable-activity' )
                )
            ),
            array(
                'name'  => 'comment_allowed_time',
                'label' => __( 'Allow editing only before the given time', 'bp-editable-activity' ),
                'desc'  => __( 'Put a number. This is the number of minute after posting of activity. Zero means no limit' ),
                'type'  => 'text',
                'default' => 10,
                
            ),
               
            ));
        
      
       
        $page->init();
        
    }

    function admin_menu() {
        add_options_page( __( 'BP Editable Activity', 'bp-editable-0activity' ), __( 'BP Editable Activity', 'bp-editable-activity' ), 'manage_options', 'bp-editable-activity', array($this->page, 'render') );
    }

    

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    
    public function admin_css(){
        
        if( !isset( $_GET['page'] ) || $_GET['page'] !='bp-editable-activity' )
            return;
        
        ?>

<style type="text/css">
    .wrap .form-table{
        margin:10px;
    }
    
</style>

   <?php     
        
    } 


}

new BP_Editable_Activity_Admin();