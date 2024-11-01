/** this is js file **/

jQuery(function() {
    jQuery("#resetForm").click(function(){
        jQuery("#momentTimeContainer").hide();
        jQuery("#pagesParent").show();
        jQuery("#postsParent").show();
    });
    jQuery("#addCampaign").validate({
        submitHandler:function(){
            /*console.log("form passes");*/
            var campaign_data = jQuery("#addCampaign").serialize();
            jQuery.post(ajaxurl, campaign_data, function(response) {
                /*console.log(response);*/
                if( response.Status == "true" ) {
                    var url = window.location.protocol;
                    url += "//"+window.location.host;
                    /*if(window.location.port)
                        url += ":" + window.location.port;*/
                    var pathArray = window.location.pathname.split('/');
                  //  console.log(pathArray);
                    pathArray.shift();
                    for (i = 0; i < pathArray.length; i++) {
                        url += "/";
                        url += pathArray[i];
                    }
                    if(response.Type == "Insert")
                        url += "?page=tapcliq&success=1";
                    else
                        url += "?page=tapcliq&updatesuccess=1";
                    window.location.href=url;
                }
                else {
                    jQuery('#errorMessage').show();
                    jQuery('#errorMessage p strong').text(response.Message);
                    jQuery(window).scrollTop(0);
                }
            })
                .fail(function(response) {
                  //  console.log( "error" + response);
                });
        }
    });
    jQuery('input[type=radio][name=configuration]').change(function() {
       var selectedConfiguration = this.value;
        if (selectedConfiguration.localeCompare("0") == 0) {
            jQuery("#pagesParent").hide();
            jQuery("#postsParent").hide();
        }
        else if (selectedConfiguration.localeCompare("1") == 0) {
            jQuery("#pagesParent").show();
            jQuery("#postsParent").show();
        }
    });

    jQuery("#onMoment").change(function() {
        var selectedOnMoment = this.value;
        if(selectedOnMoment == 1)
            jQuery("#momentTimeContainer").show();
        else
            jQuery("#momentTimeContainer").hide();
    });
});