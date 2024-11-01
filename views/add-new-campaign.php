<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12/19/2018
 * Time: 3:39 PM
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    $action = isset($_GET['action']) ? trim($_GET['action']) : "";

    $campaignId;

    if($action == "edit")
    {
        global  $campaignId;
        $campaignId = isset($_GET['campaign']) ? intval($_GET['campaign']) : 0;
        $campaign_data = TapcliqDataBaseHelper::getCampaignById($campaignId);
        $campaign_data = reset($campaign_data);

    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $campaignId ? "Update Campaign" : "Add New Campaign" ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        #addCampaign label.error{color:#ff0000}
    </style>
</head>
<body>
<div id = "errorMessage" class="notice notice-error" style="display: none;">
    <p><strong>Error Message.</strong></p>
</div>
<div class="container form-container">
    <h2><?php echo $campaignId ? "Update Campaign" : "Add New Campaign" ?></h2>
    <form action="/tapcliq.php" id = "addCampaign">
        <input type="hidden" id="id" name="id" value="<?php echo $campaignId; ?>">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" class="form-control inputtext" id="title" required name="title" maxlength="100" placeholder="Enter title"
                   value = "<?php echo $campaignId ? $campaign_data->title : ""?>">
        </div>
        <div class="form-group">
            <label for="appId">AppId:</label>
            <input type="text" class="form-control inputtext" id="appId" name="appId" required maxlength="40" placeholder="Enter App Id"
            value = "<?php echo $campaignId ? $campaign_data->appid : ""?>">
        </div>
        <div class="form-group">
            <label for="tags">Tags:</label>
            <input type="text" class="form-control inputtext" id="tags" name="tags" required maxlength="1000"
                   placeholder="Enter tags(comma separated)" value = "<?php echo $campaignId ? $campaign_data->tags : ""?>">
        </div>
        <div class="form-group">
            <label for="height_width">Height X Width:</label>
            <select class="form-control dropdown" id="height_width" name="height_width">
                <option value = "0" <?php echo $campaignId ? ($campaign_data->height_width == 0 ? "selected" : "") : "" ?>>300 X 250</option>
                <option value = "1" <?php echo $campaignId ? ($campaign_data->height_width == 1 ? "selected" : "") : "" ?>>300 x 50</option>
            </select>
        </div>
        <div class="form-group">
            <label for="unitId">UnitId:</label>
            <?php wp_nonce_field("add_campaign_unitid");?>
            <input type="number" class="form-control inputnumber" id="unitId" name="unitId" min="0" max="999999"
                   value = "<?php echo $campaignId ? $campaign_data->unitid : "0"?>" >
        </div>
        <div class="form-group">
            <fieldset class="form-group">
               <!-- <legend>Configuration</legend>-->
                <label for="Configuration">Configuration:</label>
                <div class="form-check">
                    <label class="form-check-label" style="font-weight: 500;">
                        <input type="radio" class="form-check-input" name="configuration" id="configuration_all_page" value="0"
                            <?php echo $campaignId ? ($campaign_data->configuration == 0 ? "checked" : "") : "" ?>>
                        All Page
                    </label>
                </div>
                <div class="form-check">
                    <label class="form-check-label" style="font-weight: 500;">
                        <input type="radio" class="form-check-input" name="configuration" id="configuration_custom_page" value="1"
                            <?php echo $campaignId ? ($campaign_data->configuration == 1 ? "checked" : "") : "checked" ?>>
                        Custom
                    </label>
                </div>
            </fieldset>
        </div>
        <div class="form-group" id="pagesParent" <?php echo $campaignId ? ($campaign_data->configuration == 1 ? "style=display:block;" : "style=display:none;") : "style=display:block;"?>>
            <label for="pages">Pages:</label>
            <div class="form-group" id="pages" >
                <?php
                $isUpdate = false;
                if($campaignId && $campaign_data->configuration==1) {
                    $isUpdate = true;
                    $selectedPages = array_filter(explode(",", $campaign_data->page));
                }
                if ( $pages = get_pages( $args )) {
                    foreach ( $pages as $page ) {?>
                        <input id="page_<?php echo $page->ID; ?>" type="checkbox" name="pages[]" value="<?php echo $page->ID; ?>"
                            <?php if($isUpdate) {
                                if ( in_array($page->ID, (array) $selectedPages) ) {
                                    ?> checked <?php
                                }
                            } else{
                                if ( in_array($page->ID, (array) $pages) ) {
                                    ?> checked <?php
                                }
                            } ?> /> <label style="font-weight: 500;" for="page_<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></label> <br>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <div class="form-group" id="postsParent" <?php echo $campaignId ? ($campaign_data->configuration == 1 ? "style=display:block;" : "style=display:none;") : "style=display:block;" ?>>
            <label for="posts">Posts:</label>
            <div class="form-group" id="posts" >
                <?php
                /*$args = array(
                    'post_type'=> 'post',
                    'orderby'    => 'name',
                    'post_status' => 'publish',
                    'order'    => 'ASC',
                    'posts_per_page' => -1 // this will retrive all the post that is published
                );*/
                $allPosts = get_posts(array(
                    "post_type" => "post",
                    "post_status" => "publish"
                ));

                $isUpdate = false;
                if($campaignId && $campaign_data->configuration==1) {
                    $isUpdate = true;
                    $selectedPosts = array_filter(explode(",", $campaign_data->post));
                }

                if ( $allPosts ) {
                    foreach ( $allPosts as $post ) {?>
                        <input id="post_<?php echo $post->ID; ?>" type="checkbox" name="posts[]" value="<?php echo $post->ID; ?>"
                            <?php  if($isUpdate) {
                                if ( in_array($post->ID, (array) $selectedPosts) ) {
                                    ?> checked <?php
                                }
                            } else{
                                if ( in_array( $post->ID, (array) $posts) ) {
                                    ?> checked <?php
                                }
                            } ?> /> <label style="font-weight: 500;" for="post_<?php echo  $post->ID; ?>"><?php echo  $post->post_title; ?></label> <br>
                        <?php
                    }
                }

                /*$posts = new WP_Query( $args );
                if ( $posts-> have_posts() ) {
                    while ($posts->have_posts()) : $posts->the_post();  */?><!--
                        <input id="post_<?php /*echo the_ID(); */?>" type="checkbox" name="posts[]" value="<?php /*echo the_ID(); */?>" <?php /*if ( in_array(the_ID(), (array) $posts) ) { */?> checked <?php /*} */?>/> <label for="post_<?php /*echo the_ID(); */?>"><?php /*echo the_title(); */?></label> <br>

                --><?php
/*                    endwhile;
                } wp_reset_postdata();*/
                ?>
            </div>
        </div>
        <!--<div class="form-group" id="posts">
            <label for="posts">Posts:</label>
            <select class="form-control" id="posts" name="posts" multiple>
                <option>Home</option>
                <option>404</option>
            </select>
        </div>-->
        <div class="form-group">
            <label for="onLocation">Location:</label>
            <?php wp_nonce_field("add_campaign_location");?>
            <select class="form-control" id="onLocation" name="onLocation">
                <option value = "0" <?php echo $campaignId ? ($campaign_data->location == 0 ? "selected" : "") : "" ?>>Bottom Left</option>
                <option value = "1" <?php echo $campaignId ? ($campaign_data->location == 1 ? "selected" : "") : "" ?>>Bottom Right</option>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Status:</label>
            <select class="form-control" id="status" name="status">
                <option value="1"  <?php echo $campaignId ? ($campaign_data->is_active == 1 ? "selected" : "") : "" ?>>Enabled</option>
                <option value="0"  <?php echo $campaignId ? ($campaign_data->is_active == 0 ? "selected" : "") : "" ?>>Disabled</option>
            </select>
        </div>
        <div class="form-group">
            <label for="onMoment">Moment:</label>
            <select class="form-control" id="onMoment" name="onMoment">
                <option value="0" <?php echo $campaignId ? ($campaign_data->on_moment == 0 ? "selected" : "") : "" ?>>End of Page</option>
                <option value="1" <?php echo $campaignId ? ($campaign_data->on_moment == 1 ? "selected" : "") : "" ?>>After Specific Time</option>
                <option value="2" <?php echo $campaignId ? ($campaign_data->on_moment == 2 ? "selected" : "") : "" ?>>OnLoad</option>
            </select>
        </div>
        <div class="form-group" id="momentTimeContainer"  <?php echo $campaignId ? ($campaign_data->on_moment == 1 ? "style=display:block;" : "style=display:none;") : "style=display:none;" ?>>
            <label for="momentTime">Time(In Milli.):</label>
            <input type="number" class="form-control" id="momentTime" name="momentTime" min="0" max="600000"
                   value = "<?php echo $campaignId ? $campaign_data->moment_time : "6000" ?>">
        </div>
        <input type="hidden" id="action" name="action" value="<?php echo $campaignId ? "update_campaign" : "add_campaign"; ?>">
        <?php wp_nonce_field("campaign_action_nonce","add_campaign_nonce");?>
        <button type="submit" class="btn btn-default" id="add_new_tapcliq_campaign">Save</button>
        <button type="reset" class="btn btn-default" id="resetForm" <?php echo $campaignId ? "style=display:none;" : ""?>>Reset</button>
       <a href="?page=tapcliq"><button type="button" class="btn btn-default" id="cancel">Cancel</button></a>
    </form>
</div>

</body>
</html>