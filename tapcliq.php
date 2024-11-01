<?php
/**
 * Plugin Name:       tapCLIQ
 * Description:       Enable guided seller, support and engagement bots on your website
 * Version:           2.0.0
 * Author:            tapCLIQ
 * Author URI:        https://www.tapcliq.com/
 * Text Domain:       tapcliq
 * License:
 * License URI:       https://www.tapcliq.com/termsandcondition
 */

/*
 * Plugin constants
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//constanst
define ("TAPCLIQ_DIR_PATH", plugin_dir_path(__FILE__));
define ("TAPCLIQ_URL", plugins_url());
define ("TAPCLIQ_PLUGIN_VERSION", "1.0.0");
define ("PLUGIN_TAPCLIQ_SCRIPT_VERSION", "1.0.0");
include( TAPCLIQ_DIR_PATH . 'datahelper/DBHelper.php');
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

//echo "your plug in is activated, hurrreyyyyyyyyyyyyyyyyy";
//menu hook add menu to the menu list
// we registered two menus all campaign and add new campaign
add_action("admin_menu", "tapcliq_add_menu");
function tapcliq_add_menu() {
    $hook = add_menu_page(
        "tapCLIQ",  //page title
        "tapCLIQ",  // menu title
        "manage_options",   // admin level
        "tapcliq",  //slug
        "tapcliq_all_campaign",   //callback function
        plugins_url('/assets/images/tapcliq.png', __FILE__),

        6   // position in menu
    );
    add_action( "load-$hook", 'add_screen_options' );

    function add_screen_options() {
        $option = 'per_page';
        $args = array(
            'label' => 'Number of items per page:',
            'default' => 10,
            'option' => 'campaigns_per_page'
        );
        add_screen_option( $option, $args );

    }

    add_filter('set-screen-option', 'set_screen_option', 10, 3);

    function set_screen_option($status, $option, $value) {
        if ( 'campaigns_per_page' == $option ) return $value;
        return $status;
    }

    add_submenu_page(
        "tapcliq",  //parent slug
        "All Campaign",  // page title
        "All Campaign",   // menu title
        "manage_options",  //capability = user_level access
        "tapcliq",   //menu slug
        "tapcliq_all_campaign" // call back function
    );

    add_submenu_page(
        "tapcliq",  //parent slug
        "Add New",  // page title
        "Add New",   // menu title
        "manage_options",  //capability = user_level access
        "add-new-campaign",   //menu slug
        "tapcliq_add_new_campaign" // call back function
    );
}
//called on all campaign menu click
function tapcliq_all_campaign() {
    //all campaign function
/*    require_once TAPCLIQ_DIR_PATH."/modal/campaign-list.php";*/

    require( dirname( __FILE__ ) . '/modal/campaign-list.php' );

    $campaignList = new Campaign_List();
    echo '<div class="wrap"><h2>Campaigns' . ' <a href='. currentUrl(true) .'?page=add-new-campaign class="page-title-action">Add New</a></h2>';
    if ( isset($_GET['success']) && $_GET['success'] == 1 )
    {
    echo '<div id="successMessage" class="notice notice-success is-dismissible" style="display: block">';
        echo '<p><strong>Campaign added</strong></p>';
        echo '<button type="button" class="notice-dismiss">';
            echo '<span class="screen-reader-text">Dismiss this notice.</span>';
        echo '</button>';
    echo '</div>';
    }
    if ( isset($_GET['updatesuccess']) && $_GET['updatesuccess'] == 1 )
    {

        echo '<div id="successMessage" class="notice notice-success is-dismissible" style="display: block">';
        echo '<p><strong>Campaign updated</strong></p>';
        echo '<button type="button" class="notice-dismiss">';
        echo '<span class="screen-reader-text">Dismiss this notice.</span>';
        echo '</button>';
        echo '</div>';
    }

    $campaignList->prepare_items();
    echo "<form method = 'post' name = 'frm_search_campaign' action = '".$_SERVER['PHP_SELF'] . "?page=tapcliq'>";
    $campaignList->search_box("Search Campaign", "search_campaign_id");
    $campaignList->display();
    echo "</form>";
    echo '</div>';
}

function currentUrl( $trim_query_string = true ) {
    $pageURL = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
    $pageURL .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER["REQUEST_URI"];
    if( ! $trim_query_string ) {
        return $pageURL;
    } else {
        $url = explode( '?', $pageURL );
        return $url[0];
    }
}

//called on add new campaign menu click
function tapcliq_add_new_campaign() {
    //add new campaign function
    include TAPCLIQ_DIR_PATH."/views/add-new-campaign.php";
}

//called on activation of plugin
// here we included our css and js file
add_action("init", "tapcliq_plugin_assets");
function tapcliq_plugin_assets() {
    //css file
    wp_enqueue_style(
        "tapcliq_style",    //unique name for css file
        plugins_url("/assets/css/style.css", __FILE__), //css file path
        '', //dependency on other files
        TAPCLIQ_PLUGIN_VERSION // plugin version number
    );
    //js file
    wp_enqueue_script(
        "tapcliq_script",    //unique name for js file
        plugins_url("/assets/js/script.js", __FILE__), //js file path
        '', //dependency on other files
        TAPCLIQ_PLUGIN_VERSION, // plugin version number
        true    // required in footer section
    );

    /*wp_enqueue_script(
        "tapcliq_unitexecution_script",    //unique name for js file
        TAPCLIQ_URL."/tapcliq/assets/js/tapcliq.js", //js file path
        '', //dependency on other files
        PLUGIN_TAPCLIQ_SCRIPT_VERSION, // plugin version number
        true    // required in footer section
    );*/
}

add_action( 'template_redirect', 'bind_unitdata_to_display' );
function bind_unitdata_to_display() {
    // Register our script
    wp_register_script(
        "tapcliq_unitexecution_script",    //unique name for js file

        plugins_url("/assets/js/tapcliq.js", __FILE__),//js file path
        '', //dependency on other files
        PLUGIN_TAPCLIQ_SCRIPT_VERSION, // plugin version number
        true    // required in footer section
    );

    $page_object = get_queried_object();
    $page_id = get_queried_object_id();
    $dbHelper = new TapcliqDataBaseHelper();
    $campaign = $dbHelper->getCustomCampaingsForPageorPostId($page_id);
    if(!count($campaign)) {
        global $campaign;
        $campaign = $dbHelper->getAllPageCampaings();
    }
    if(count($campaign))
    {
        $campaign = reset($campaign);
        $campaignData = [
            'appId' => $campaign->appid,
            'tags' => $campaign->tags,
            'height_width' => $campaign->height_width,
            'unitId' => $campaign->unitid,
            'location' => $campaign->location,
            'moment' => $campaign->on_moment,
            'momentTime' => $campaign->moment_time,
            'isAdmin' => is_admin() ? 'true' : 'false',
        ];

        // Localise the data, specifying our registered script and a global variable name to be used in the script tag
        wp_localize_script('tapcliq_unitexecution_script', 'campaignData', $campaignData);


        // Enqueue our script (this can be done before or after localisation)
        wp_enqueue_script('tapcliq_unitexecution_script');
    }
}


//called on plugin activation
//created tapcliq table on activation of plugin
register_activation_hook(__FILE__, "on_tapcliq_plugin_activation");
function on_tapcliq_plugin_activation() {
    global $wpdb;
    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
    if (count($wpdb->get_var("SHOW TABLES LIKE '$tapcliq_campaign'")) == 0) {
        $create_tapcliq_table = "CREATE TABLE " . $tapcliq_campaign . "(
      `entity_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Grid Record Id',
      `title` varchar(255) NOT NULL COMMENT 'Title',
      `appid` mediumtext NOT NULL COMMENT 'Appid',
      `tags` mediumtext NOT NULL COMMENT 'Tags',
      `height_width` mediumtext NOT NULL COMMENT 'Height_Width',
      `unitid` mediumtext NOT NULL COMMENT 'Unitid',
      `configuration` smallint(6) DEFAULT NULL COMMENT 'Configuration',
      `page` mediumtext NOT NULL COMMENT 'Page',
      `post` mediumtext NOT NULL COMMENT 'Post',
      `location` mediumtext NOT NULL COMMENT 'Location',
      `is_active` smallint(6) DEFAULT NULL COMMENT 'Active Status',
      `on_moment` smallint(6) DEFAULT NULL COMMENT 'on Moment',
      `moment_time` text COMMENT 'Moment Time',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation Time',
      `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Modification Time'
      )";
        dbDelta($create_tapcliq_table);
    }
}

// called on plugin deactivation
//we drop table if we want on deactivation of plugin
register_activation_hook(__FILE__, "on_tapcliq_deactivate");
function on_tapcliq_deactivate() {
    //delete created table
    /*global $wpdb;
    $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
    $wpdb->query("DROP TABLE IF EXISTS " . $tapcliq_campaign);*/
}

// called on plugin delete
//we drop table if we want on deactivation of plugin
register_uninstall_hook(__FILE__, "on_tapcliq_delete");
function on_tapcliq_delete() {
    //delete created table
    global $wpdb;
    $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
    $wpdb->query("DROP TABLE IF EXISTS " . $tapcliq_campaign);
}

//ajax call bind with function
add_action('wp_ajax_nopriv_getStateList', 'add_tapcliq_campaign_data');
add_action("wp_ajax_add_campaign", "add_tapcliq_campaign_data");
add_action("wp_ajax_update_campaign", "add_tapcliq_campaign_data");

function add_tapcliq_campaign_data() {
    include TAPCLIQ_DIR_PATH."/datahelper/data-helper.php";
    $response= add_new_tapcliq_campaign($_REQUEST);
    echo $response;
    /*if( $response->Status) {
        return $response->Message;
    } else {
        return $response->Message;
    }*/
    wp_die();
}

function tapcliq_admin_bootstrap_enqueue_style_js($hook)
{
    if($hook == 'tapcliq_page_add-new-campaign') {
        wp_enqueue_style(
            "bootstrap-min-css",    //unique name for css file
            plugins_url("/assets/css/bootstrap.min.css", __FILE__), //css file path
            '', //dependency on other files
            "3.3.7" // version number
        );
    }
   /* wp_enqueue_script(
        "jquery-min-js",    //unique name for js file
        TAPCLIQ_URL."/tapcliq/assets/js/jquery.min.js",
        '', //dependency on other files
        "3.3.1", // version number
        true
    );*/
    wp_enqueue_script(
        "jquery-validate-js",    //unique name for js file
        plugins_url("/assets/js/jquery.validate.min.js", __FILE__), //js file path

        '', //dependency on other files
        "1.16.0", // version number
        true
    );
}
add_action( 'admin_enqueue_scripts', 'tapcliq_admin_bootstrap_enqueue_style_js' );