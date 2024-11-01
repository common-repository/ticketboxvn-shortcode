<?php
/**
 * Plugin Name: Ticketbox Shortcode
 * Plugin URI: http://joshkopecek.co.uk
 * Description: Adds the code to include Ticketbox buttons and iframe
 * Version: 1.1
 * Author: Josh Kopecek
 * Author URI: http://joshkopecek.co.uk
 * @package Display Posts
 * @version 1.1
 * @author Josh Kopecek
 * @copyright Copyright (c) 2013, Josh Kopecek
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// Create the shortcode
add_shortcode( 'ticketbox', 'ticketbox_shortcode' );
function ticketbox_shortcode( $atts ) {

	// Original Attributes, for filters
	$original_atts = $atts;

	// Pull in shortcode attributes and set defaults
	$atts = shortcode_atts( array(
		'id'              => false,
		'type'			  => 'iframe',
		'height'		  => '380',
		'width'			  => '100%',
        'campaignid'      => ''
	), $atts );

	// ticketbox affiliate code
	$ticket_options = get_option('ticketbox_option_name');
	$fid = $ticket_options['affiliate_id'];
	
	$id = $atts['id']; // Sanitized later as an array of integers
	$type = $atts['type']; // Type of integration - iframe or button
    $campaignid = $atts['campaignid']; // Campaign ID
	
	if ($id) {
		switch ($type) {
			case 'button': // checks what type of listing is specified
				$ticketbox = '<div class="post-list-text">  <!-- latest posts list --> <div id="latest-posts">';
				break;
			case 'iframe':
				$ticketbox = '<iframe frameborder="0" style="display:block;" height="' . $atts['height'] . '" width="100%" src="http://ticketbox.vn/ticket-booking/' . $id . '/widget';
				if ($campaignid) $ticketbox .= '?ac=' . $campaignid;
				$ticketbox .= '"></iframe>';
				break;
			default:
				$ticketbox = '';
		}
	} else {
		$ticketbox = '<a href="http://ticketbox.vn/">TicketBox</a>';
	}
	
	$return = $ticketbox;

	return $return;
}

class TicketBox_Settings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'TicketBox Settings', 
            'manage_options', 
            'ticketbox-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'ticketbox_option_name' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>TicketBox Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'ticketbox_option_group' );   
                do_settings_sections( 'ticketbox-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'ticketbox_option_group', // Option group
            'ticketbox_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Affiliate Section', // Title
            array( $this, 'print_section_info' ), // Callback
            'ticketbox-admin' // Page
        );  

        add_settings_field(
            'affiliate_id', // ID
            'Affiliate ID', // Title 
            array( $this, 'affiliate_id_callback' ), // Callback
            'ticketbox-admin', // Page
            'setting_section_id' // Section           
        );          
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['affiliate_id'] ) )
            $new_input['affiliate_id'] = absint( $input['affiliate_id'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter TicketBox settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function affiliate_id_callback()
    {
        printf(
            '<input type="text" id="affiliate_id" name="ticketbox_option_name[affiliate_id]" value="%s" />',
            isset( $this->options['affiliate_id'] ) ? esc_attr( $this->options['affiliate_id']) : ''
        );
    }

}

if( is_admin() )
    $my_settings_page = new TicketBox_Settings();