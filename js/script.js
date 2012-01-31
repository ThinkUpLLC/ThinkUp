$(document).ready(function()
{
	// Add to circle
	gapi.plus.go();
	
	// Slideshow
    $("#showcase").awShowcase(
    {
        content_width:  978,
        content_height: 300,
        auto: true,
        continuous: true,
        interval: 5000,
        fit_to_parent: true,
        buttons: false,
        arrows: false
    });
});