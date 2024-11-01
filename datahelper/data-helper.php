<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12/28/2018
 * Time: 5:00 PM
 */
//include ("DBHelper.php");

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function add_new_tapcliq_campaign($campaign_data) {
    if ( ! wp_verify_nonce( $campaign_data['add_campaign_nonce'], "campaign_action_nonce" )) {
        die( 'Security check' );

    } else {

        $dbHelper = new TapcliqDataBaseHelper();
        if (!$dbHelper->isTapcliqCampaignTableExist())
            $dbHelper->createTapcliqCampaignTable();
        $posts = ",";
        $pages = ",";
        $isAllPageCampaignExist = 0;
        if ($campaign_data['onMoment'] != 1) {
            $campaign_data['momentTime'] = "0";
        }
        if ($campaign_data['configuration'] == 0) {
            $posts = "0";    //if all page campaign then make pages 0
            $pages = "0";    //if all page campaign then make posts 0
            $isAllPageCampaignExist = $dbHelper->isAllPageCampaignExist();
            if ($isAllPageCampaignExist > 0 && $isAllPageCampaignExist != $campaign_data['id']) {
                $response = array("Status" => "false", "Message" => "Already exists a campaign with all pages, can't create another!");
                header('Content-type: application/json');
                return json_encode($response);
            }

            if ($campaign_data['id']) {
                $isSuccess = $dbHelper->updateCampaign($campaign_data, $posts, $pages);
                if ($isSuccess) {
                    $response = array("Status" => "true", "Message" => "Campaign updated successfully", "Type" => "Update");
                    header('Content-type: application/json');
                    return json_encode($response);
                } else {
                    $response = array("Status" => "true", "Message" => "Campaign updation failed, please try again", "Type" => "Update");
                    header('Content-type: application/json');
                    return json_encode($response);
                }
            } else {
                $isSuccess = $dbHelper->insertCampaign($campaign_data, $posts, $pages);
                if ($isSuccess) {
                    $response = array("Status" => "true", "Message" => "Campaign added successfully", "Type" => "Insert");
                    header('Content-type: application/json');
                    return json_encode($response);
                } else {
                    $response = array("Status" => "true", "Message" => "Campaign insertion failed, please try again", "Type" => "Insert");
                    header('Content-type: application/json');
                    return json_encode($response);
                }

            }
        } else {
            if (array_key_exists('pages', $campaign_data)) {
                foreach ($campaign_data['pages'] as $page) {
                    $pages = $pages . $page . ",";
                }
            }
            if (array_key_exists('posts', $campaign_data)) {
                foreach ($campaign_data['posts'] as $post) {
                    $posts = $posts . $post . ",";
                }
            }
        }
        if ($campaign_data['configuration'] == 1) {
            $allpages = get_pages(null);
            $pagesList = [];
            $postList = [];
            $reusedPageIds = [];
            $reusedPostIds = [];
            foreach ($allpages as $page) {
                $pagesList[$page->ID] = $page->post_title;
            }
            $postList = $dbHelper->getAllPosts();
            if ($campaign_data['id'])
                $allUsedPagesandPosts = $dbHelper->getAllCustomCampaigns($campaign_data['id']);     //update campaign id so get all campaigns except current update campaign
            else
                $allUsedPagesandPosts = $dbHelper->getAllCustomCampaigns("-1");                 // get all custom campaigns
            if (!array_key_exists('pages', $campaign_data) && !array_key_exists('posts', $campaign_data)) {
                $response = array("Status" => "false", "Message" => "Required at least 1 post or page");
                header('Content-type: application/json');
                return json_encode($response);
            }
            if (array_key_exists('pages', $campaign_data)) {
                global $reusedPageIds;
                $usedPageList = array_filter(explode(',', $allUsedPagesandPosts["Pages"])); //converted used pages id in array
                $usedPageList = array_unique($usedPageList);  // removed duplicate entry from used pages id
                $reusedPageIds = array_intersect($usedPageList, $campaign_data['pages']); // find already exist page ids
            }
            if (array_key_exists('posts', $campaign_data)) {
                global $reusedPostIds;
                $usedPostList = array_filter(explode(',', $allUsedPagesandPosts["Posts"])); //converted used post ids in array
                $usedPostList = array_unique($usedPostList);  // removed duplicate entry of post ids
                $reusedPostIds = array_intersect($usedPostList, $campaign_data['posts']); // find already exist post ids
            }
            if (count($reusedPageIds) && count($reusedPostIds)) {
                $errorMSG = "Campaign already exist with " . usedPagesorPostNames($reusedPageIds, null, $pagesList) . " and " . usedPagesorPostNames($reusedPostIds, $postList, null);
                $response = array("Status" => "false", "Message" => $errorMSG);
                header('Content-type: application/json');
                return json_encode($response);
            } elseif (count($reusedPageIds)) {
                $errorMSG = "Campaign already exist with " . usedPagesorPostNames($reusedPageIds, null, $pagesList);
                $response = array("Status" => "false", "Message" => $errorMSG);
                header('Content-type: application/json');
                return json_encode($response);
            } else if (count($reusedPostIds)) {
                $errorMSG = "Campaign already exist with " . usedPagesorPostNames($reusedPostIds, $postList, null);
                $response = array("Status" => "false", "Message" => $errorMSG);
                header('Content-type: application/json');
                return json_encode($response);
            } else {
                if ($campaign_data['id']) {
                    $isSuccess = $dbHelper->updateCampaign($campaign_data, $posts, $pages);
                    if ($isSuccess) {
                        $response = array("Status" => "true", "Message" => "Campaign updated successfully", "Type" => "Update");
                        header('Content-type: application/json');
                        return json_encode($response);
                    } else {
                        $response = array("Status" => "true", "Message" => "Campaign updation failed, please try again", "Type" => "Update");
                        header('Content-type: application/json');
                        return json_encode($response);
                    }
                } else {
                    $isSuccess = $dbHelper->insertCampaign($campaign_data, $posts, $pages);
                    if ($isSuccess) {
                        $response = array("Status" => "true", "Message" => "Campaign added successfully", "Type" => "Insert");
                        header('Content-type: application/json');
                        return json_encode($response);
                    } else {
                        $response = array("Status" => "true", "Message" => "Campaign insertion failed, please try again", "Type" => "Insert");
                        header('Content-type: application/json');
                        return json_encode($response);
                    }
                }
            }
        }
    }
    $response = array("Status" => "true", "Message" => "Not valid user");
    header('Content-type: application/json');
    return json_encode($response);
}

function usedPagesorPostNames($usedIds, $postList, $pageList) {
    $usedPostorPages = "";
    $last_id = end($usedIds);
    foreach($usedIds as $id) {
        if($id == $last_id) {
            $usedPostorPages = $usedPostorPages . ($postList != null ? $postList[$id] : $pageList[$id]);
        }
        else {
            $usedPostorPages = $usedPostorPages . ($postList != null ? $postList[$id] : $pageList[$id]) . ", ";
        }
    }
    return ($postList != null ? ((count($usedIds)>1 ? "Posts " : "Post ") . $usedPostorPages) : ((count($usedIds)>1 ? "Pages " : "Page ") . $usedPostorPages));
}