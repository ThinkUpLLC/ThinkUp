$(document).ready(function(){	
    $(".with_tooltip").easyTooltip();
});
$('a.with_tooltip').click(function(){
    var willWorkOnID = $(this).attr("willWorkOnID");
    var value = $(this).attr("value");
    $("div[id^=location] > div").show();
    $('[class^=show]').hide();
    // Condition to display the appropriate button
    if (willWorkOnID == 'locationReplies') {
        $('.show_replies').show();
    } else {
        $('.show_forwards').show();
    }
    $("#" + willWorkOnID + " > div").hide();
    $("." + value).show();
});
$('a.[class^=show]').click(function(){
    $("div[id^=location] > div").show();
    $('a.[class^=show]').hide();
});
$('a#sortOutreachReplies').click(function() {
    $('.sort_replies').hide();
    $('.default_replies').show();
    $('a#sortProximityReplies').removeClass('bold');
    $('a#sortOutreachReplies').addClass('bold');
});
$('a#sortProximityReplies').click(function() {
    $('.sort_replies').show();
    $('.default_replies').hide();
    $('a#sortProximityReplies').addClass('bold');
    $('a#sortOutreachReplies').removeClass('bold');
});
$('a#sortOutreachRetweets').click(function() {
    $('.sort_retweets').hide();
    $('.default_retweets').show();
    $('a#sortProximityRetweets').removeClass('bold');
    $('a#sortOutreachRetweets').addClass('bold');
});
$('a#sortProximityRetweets').click(function() {
    $('.sort_retweets').show();
    $('.default_retweets').hide();
    $('a#sortProximityRetweets').addClass('bold');
    $('a#sortOutreachRetweets').removeClass('bold');
});
