<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 01/24/2019
 * Time: 7:38 PM
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TapcliqDataBaseHelper
{
    public function isTapcliqCampaignTableExist() {
        global $wpdb;
        $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
        return count($wpdb->get_var("SHOW TABLES LIKE '$tapcliq_campaign'")) == 1;
    }


    function createTapcliqCampaignTable() {
        global $wpdb;
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
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

    function isAllPageCampaignExist() {
        global $wpdb;
        $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT entity_id FROM " . $tapcliq_campaign . "
                    WHERE is_active = %d AND configuration = %d LIMIT 1",
                1, 0    //checking for all page campaign which is active 1 = enabled and 0 = all page
            )
        );
    }

    function getAllCustomCampaigns($campaignId) {
        global $wpdb;
        $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
        $allCustomCampaigns = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT page, post FROM " . $tapcliq_campaign . "
                    WHERE is_active = %d AND configuration = %d AND entity_id <> %d",
                1, 1, $campaignId    //checking for all page campaign which is active 1 = enabled and 0 = custom configuration
            )
        );
        $usedPages = "";
        $usedPosts = "";
        $allUsedPagesandPosts = [];
        foreach ($allCustomCampaigns as $campaign) {
            $usedPages = $usedPages . $campaign->page;
            $usedPosts = $usedPosts . $campaign->post;
        }
        $allUsedPagesandPosts["Pages"]=$usedPages;
        $allUsedPagesandPosts["Posts"]=$usedPosts;
        return $allUsedPagesandPosts;
    }

    function getAllPosts() {
        /*global $wpdb;
        $post_table = $wpdb->prefix.'posts';
        $allPosts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM " . $post_table . "
                    WHERE post_type = '%s' and post_status = '%s' ORDER BY post_title ", 'post', 'publish'    //checking for all page campaign which is active 1 = enabled and 0 = custom configuration
            )
        );*/
        $allPosts = get_posts(array(
           "post_type" => "post",
            "post_status" => "publish"
        ));
        $posts = [];
        foreach ($allPosts as $post) {
            $posts[$post->ID] = $post->post_title;
        }
        return $posts;
    }

    function getCustomCampaingsForPageorPostId($id) {
        global $wpdb;
        $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
        $campaign = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $tapcliq_campaign . "
                    WHERE configuration = '%d' AND (page like '%,%d,%' or post like '%,%d,%') AND is_active = '%d' ", 1, $id, $id, 1    //checking for all page campaign which is active 1 = enabled and 0 = custom configuration
            )
        );
        return $campaign;
    }

    function getAllPageCampaings() {
        global $wpdb;
        $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
        $campaign = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $tapcliq_campaign . "
                    WHERE configuration = '%d' AND is_active = '%d' ", 0, 1    //checking for all page campaign which is active 1 = enabled and 0 = custom configuration
            )
        );
        return $campaign;
    }

    function getCampaignById($campaignId) {
        global $wpdb;
        $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
        $campaign = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $tapcliq_campaign . "
                    WHERE entity_id = '%d' ", $campaignId    //checking for all page campaign which is active 1 = enabled and 0 = custom configuration
            )
        );
        return $campaign;
    }

    function insertCampaign($post_data, $posts, $pages) {
        global $wpdb;
        $date = date('Y-m-d H:i:s');
        $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO " . $tapcliq_campaign . " (title, appid, tags, height_width, unitid, configuration, page, post, location, is_active,
                on_moment, moment_time, created_at) VALUES ('%s','%s','%s','%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s')",
                $post_data['title'], $post_data['appId'], $post_data['tags'], $post_data['height_width'],
                $post_data['unitId'], $post_data['configuration'], $pages, $posts, $post_data['onLocation'], $post_data['status'], $post_data['onMoment'],
                $post_data['momentTime'], $date
            )
        );
        return true;
    }
    function updateCampaign($post_data, $posts, $pages) {
        global $wpdb;
        $date = date('Y-m-d H:i:s');
        $tapcliq_campaign = $wpdb->prefix.'tapcliq_campaigns';
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . $tapcliq_campaign . " SET title = '%s', appid = '%s', tags = '%s', height_width = '%d', unitid = '%d', 
                configuration = '%d', page = '%s', post = '%s', location = '%d', is_active = '%d', on_moment = '%d', moment_time = '%s', updated_at = '%s' WHERE entity_id = '%d'",
                $post_data['title'], $post_data['appId'], $post_data['tags'], $post_data['height_width'],
                $post_data['unitId'], $post_data['configuration'], $pages, $posts, $post_data['onLocation'], $post_data['status'], $post_data['onMoment'],
                $post_data['momentTime'], $date, $post_data['id']
            )
        );
        return true;
    }
}