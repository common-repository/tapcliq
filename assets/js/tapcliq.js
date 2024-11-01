
if(campaignData["isAdmin"] === "false") {
   /* var jqueryScript = document.createElement("script"); //Make a script DOM node
    jqueryScript.src = "https://code.jquery.com/jquery-1.10.1.min.js"; //Set it's src to the provided URL
    document.head.appendChild(jqueryScript); //Add it to the end of the head section of the page (could change 'head' to 'body' to add it to the end of the body section instead)*/

    var script = document.createElement("script"); //Make a script DOM node
    script.src = "https://adserver.tapcliq.com/adserver/rest/resources/js/showtqad.js"; //Set it's src to the provided URL
    document.head.appendChild(script); //Add it to the end of the head section of the page (could change 'head' to 'body' to add it to the end of the body section instead)

// Here "addEventListener" is for standards-compliant web browsers and "attachEvent" is for IE Browsers.
var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
var eventer = window[eventMethod];
var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

//Listen to message from tq iFrame
eventer(messageEvent, function (e) {
    console.log("Got message from tq");
    if (e.origin === 'https://adserver.tapcliq.com' || e.origin === 'https:/tapcliqbucket.s3.amazonaws.com' ) {
        if(e.data == "change") {
            window.document.getElementById('tqframe').parentNode.focus();
        }
        else {
            closeMe(e.data);
        }
    }
}, false);
console.log(campaignData );
    var tq_appid = campaignData["appId"];
    var tq_tags = campaignData["tags"];
    var tq_adunitid = campaignData["unitId"];
    var tq_adheight = campaignData["height_width"];
    var tq_adleft = "50";  // assign default value 50 for bottom left
    var tq_adbottom = "0"; // (optional) distance from bottom of the screen for the frame
    var userId;
    var displayLocation = campaignData["location"];
    var moment = campaignData["moment"];
    var momentTime = campaignData["momentTime"];
    var isEndofpageCalled = false;
    if(displayLocation == 1)  //bottom right
    {
        tq_adleft = jQuery(document).width()-350;
    }
    if(tq_adheight == 0)  //300 X 250
    {
        tq_adheight = "250";
    }
    else if(tq_adheight == 1) //320 X 50
    {
        tq_adheight = "50";
    }
        if (moment == 0) {   //it will display unit at the end of the page(after whole page scroll down)
            jQuery(window).on('scroll.custom', function () {
                var height = jQuery(window).scrollTop();
                if (jQuery(window).scrollTop() + jQuery(window).height() >= (jQuery(document).height() - 300)) {
                    if(!isEndofpageCalled) {
                        getTqAd(tq_adheight, tq_adleft, tq_adbottom, tq_adunitid, tq_tags);
                        isEndofpageCalled = true;
                    }
                    jQuery(window).off('scroll.custom')
                }
            });
        }
        else if (moment == 1) {   //it will display unit at the end of the page(after whole page scroll down)
            /*jQuery(document).ready(function($) {
                setTimeout(function(){ getTqAd(tq_adheight, tq_adleft, tq_adbottom, tq_adunitid, tq_tags) }, momentTime);
            });*/
            document.addEventListener("DOMContentLoaded", function(event) {
                //do work
                setTimeout(function(){ getTqAd(tq_adheight, tq_adleft, tq_adbottom, tq_adunitid, tq_tags) }, momentTime);
            });
        }
        else if (moment == 2) {  //it will display unit on page load
            jQuery(window).load(function () {
                getTqAd(tq_adheight, tq_adleft, tq_adbottom, tq_adunitid, tq_tags);
            });
        }
}