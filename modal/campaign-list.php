<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
Plugin Name: WP_List_Table Class Example
Plugin URI: https://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/
Description: Demo on how WP_List_Table Class works
Version: 1.0
Author: Collins Agbonghama
Author URI:  https://w3guy.com
*/

if ( ! class_exists( 'WP_List_Table_Custom' ) ) {
    require_once( TAPCLIQ_DIR_PATH . 'modal/class-wp-list-table-custom.php' );
}

class Campaign_List extends WP_List_Table_Custom {

    /** Class constructor */
    public function __construct() {

        parent::__construct( [
            'singular' => __( 'Campaign', 'sp' ), //singular name of the listed records
            'plural'   => __( 'Campaigns', 'sp' ), //plural name of the listed records
            'ajax'     => false //does this table support ajax?
        ] );
    }


    /**
     * Retrieve campaigns data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_campaigns( $per_page = 5, $page_number = 1, $search_term = '' ) {

        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}tapcliq_campaigns";
        if(!empty($search_term)) {
            $sql .= " WHERE (title like '%" .$search_term . "%' OR appid like '%" . $search_term . "%'  OR tags like '%" . $search_term . "%') ";
        }

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }


    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public static function delete_campaign($id ) {
        global $wpdb;

        $var = $wpdb->delete(
            "{$wpdb->prefix}tapcliq_campaigns",
            [ 'entity_id' => $id ],
            [ '%d' ]
        );
        return $var;
    }


    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}tapcliq_campaigns";

        return $wpdb->get_var( $sql );
    }


    /** Text displayed when no campaign data is available */
    public function no_items() {
        _e( 'No campaigns avaliable.', 'sp' );
    }


    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {

            case 'height_width':
                return $item[ $column_name ] == 0 ? "300 X 250" : "300 X 50";
            case 'configuration':
                return $item [ $column_name ] ? "Custom" : "All Page";
            case 'location':
                return $item [ $column_name ] ? "Bottom Left" : "Bottom Right";
            case 'is_active':
                return $item [ $column_name ] ? "Enabled" : "Disabled";
            case 'on_moment':
                if($item[ $column_name ] == 0)
                    return "End of Page";
                else if($item[ $column_name ] == 1)
                    return "After Specific Time";
                else
                        return "OnLoad";

            case 'title':
                return $item[ $column_name ];
            case 'appid':
            case 'tags':
            case 'unitid':
            case 'moment_time':
            case 'created_at':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['entity_id']
        );
    }


    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_title( $item ) {

        $delete_nonce = wp_create_nonce( 'sp_delete_campaign' );

        $title = '<strong>' . $item['title'] . '</strong>';

        $actions = [
            'edit' => sprintf( '<a href="?page=%s&action=%s&campaign=%s&_wpnonce=%s">Edit</a>', "add-new-campaign", 'edit', absint( $item['entity_id'] ), $delete_nonce ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&campaign=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['entity_id'] ), $delete_nonce )
        ];

        return $title . $this->row_actions( $actions );
    }


    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'title'    => __( 'Title', 'sp' ),
            'appid' => __( 'App Id', 'sp' ),
            'tags'    => __( 'Tags', 'sp' ),
            'height_width'    => __( 'Height x Width', 'sp' ),
            'unitid'    => __( 'Unit Id', 'sp' ),
            'configuration'    => __( 'Configuration', 'sp' ),
            'location'    => __( 'Location', 'sp' ),
            'is_active'    => __( 'Status', 'sp' ),
            'on_moment'    => __( 'Moment', 'sp' ),
            'moment_time'    => __( 'Moment Time', 'sp' ),
            'created_at'    => __( 'Created On', 'sp' ),
        ];

        return $columns;
    }


    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'title' => array( 'title', false ),
            'tags' => array( 'tags', false ),
            'height_width' => array( 'height_width', false ),
            'appid' => array( 'appid', false ),
            'unitid' => array( 'unitid', false ),
            'configuration' => array( 'configuration', false ),
            'location' => array( 'location', false ),
            'is_active' => array( 'is_active', false ),
            'on_moment' => array( 'on_moment', false ),
            'moment_time' => array( 'moment_time', false ),
            'created_at' => array( 'created_at', false ),
        );

        return $sortable_columns;
    }

    public function get_hidden_columns() {
        $hidden_columns = array('entity_id');

        return $hidden_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Delete'
        ];

        return $actions;
    }


    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {
        $hidden_column = $this->get_hidden_columns();
        $sortable_column = $this->get_sortable_columns();
        $this->_column_headers = array($this->get_columns(), $hidden_column, $sortable_column);
       /* $this->_column_headers = $this->get_column_info();*/
       /* echo "<pre>";
        print_r($this->_column_headers);
        exit;*/
        /** Process bulk action */
        $this->process_bulk_action();

       // $per_page     = $this->get_items_per_page( 'customers_per_page', 5 );
        $user = get_current_user_id();
        $screen = get_current_screen();
        $option = $screen->get_option('per_page', 'option');

        $per_page = get_user_meta($user, $option, true);

        if ( empty ( $per_page) || $per_page < 1 ) {

            $per_page = $screen->get_option( 'per_page', 'default' );

        }
      //  $per_page     = $this->get_items_per_page( 'campaigns_per_page', 5 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args( [
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ] );

       $search_term = isset($_POST['s']) ? trim($_POST['s']) : "";
        $this->items = self::get_campaigns( $per_page, $current_page, $search_term);

    }



    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'sp_delete_campaign' ) ) {
                die( 'Go get a life script kiddies' );
            }
            else {
                $var = self::delete_campaign( absint( $_GET['campaign'] ) );
                if($var > 0) {
                    $message = "1 Campaign permanently deleted";
                    echo '<div id="deleteMessage" class="notice notice-success is-dismissible">';
                    echo '<p><strong>' . $message . '</strong></p>';
                    echo '<button type="button" class="notice-dismiss">';
                    echo '<span class="screen-reader-text">Dismiss this notice.</span>';
                    echo '</button>';
                    echo '</div>';
                }
                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                /*wp_redirect( esc_url_raw(add_query_arg()) );
                exit;*/
            }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
            || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

            $delete_ids = esc_sql( $_POST['bulk-delete'] );
            if(empty($delete_ids)) {
                echo '<div id="deleteMessage" class="notice notice-success is-dismissible">';
                echo '<p><strong>No campaigns selected for delete</strong></p>';
                echo '<button type="button" class="notice-dismiss">';
                echo '<span class="screen-reader-text">Dismiss this notice.</span>';
                echo '</button>';
                echo '</div>';
            }
            else {
                // loop over the array of record IDs and delete them
                $records = 0;
                foreach ($delete_ids as $id) {
                    self::delete_campaign($id);
                    $records++;
                }
                if ($records > 0) {
                    $message = $records . ($records == 1 ? " Campaign" : " Campaigns") . "  permanently deleted";
                    echo '<div id="deleteMessage" class="notice notice-success is-dismissible">';
                    echo '<p><strong>' . $message . '</strong></p>';
                    echo '<button type="button" class="notice-dismiss">';
                    echo '<span class="screen-reader-text">Dismiss this notice.</span>';
                    echo '</button>';
                    echo '</div>';
                }
            }

            // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
            // add_query_arg() return the current url
            /*wp_redirect( esc_url_raw(add_query_arg()) );
            exit;*/
        }
    }

    public function currentUrl( $trim_query_string = true ) {
        $pageURL = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        if( ! $trim_query_string ) {
            return $pageURL;
        } else {
            $url = explode( '?', $pageURL );
            return $url[0];
        }
    }

}


/*class SP_Plugin1 {

    // class instance
    static $instance;

    // customer WP_List_Table object
    public $customers_obj;

    // class constructor
    public function __construct() {
        add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
        add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
    }


    public static function set_screen( $status, $option, $value ) {
        return $value;
    }

    public function plugin_menu() {

        $hook = add_menu_page(
            'Sitepoint WP_List_Table Example',
            'SP WP_List_Table',
            'manage_options',
            'wp_list_table_class',
            [ $this, 'plugin_settings_page' ]
        );

        add_action( "load-$hook", [ $this, 'screen_option' ] );

    }


    /**
     * Plugin settings page
     */
    /*public function plugin_settings_page() {
        */?><!--
        <div class="wrap">
            <h2>WP_List_Table Class Example</h2>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
/*                                $this->customers_obj->prepare_items();
                                $this->customers_obj->display(); */?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        --><?php
/*    }

    /**
     * Screen options
     */
   /* public function screen_option() {
        $option = 'per_page';
        $args   = [
            'label'   => 'Customers',
            'default' => 5,
            'option'  => 'customers_per_page'
        ];

        add_screen_option( $option, $args );

        $this->customers_obj = new Customers_List1();
    }


    /** Singleton instance */
   /* public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}

add_action( 'plugins_loaded', function () {
    SP_Plugin1::get_instance();
} );*/


