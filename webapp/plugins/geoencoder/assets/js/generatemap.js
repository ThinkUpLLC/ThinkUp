/**
 * Script to read the json data passed to it and automatically generate google maps
 * showing the locations of replies and retweets alongwith the post that came from there.
 * @author Ekansh Preet Singh, Mark Wilkie
 */

   var map = null;
   var markerClusterer = null;
   var markers = [];

   function $(element) {
     return document.getElementById(element);
   }
  
   function initializeMap() {
       if(GBrowserIsCompatible()) {
         map = new GMap2($('map'));
         map.setCenter(new GLatLng(latlng[0],latlng[1]), 2);
         map.addControl(new GLargeMapControl3D());
         map.enableScrollWheelZoom();
         showMarkers();
       }
   }
   
   function showMarkers() {
     map.clearOverlays();
     markers = [];
     
     // Define marker icons
     var blueicon = new GIcon(G_DEFAULT_ICON);
     blueicon.image = "http://gmaps-samples.googlecode.com/svn/trunk/demo/bluemarker.png";
     var greenicon = new GIcon(G_DEFAULT_ICON);
     greenicon.image = "http://gmaps-samples.googlecode.com/svn/trunk/markers/green/blank.png";
     var redicon = new GIcon(G_DEFAULT_ICON);
     redicon.image = "http://gmaps-samples.googlecode.com/svn/trunk/markers/red/blank.png";
     var orangeicon = new GIcon(G_DEFAULT_ICON);
     orangeicon.image = "http://gmaps-samples.googlecode.com/svn/trunk/markers/orange/blank.png";
     var selectedicon = new GIcon(G_DEFAULT_ICON);
     
     if(markerClusterer != null) {
       markerClusterer.clearMarkers();
     }

     var panel;
     var listname;
     for (var i = 0; i < locations.length; i++) {
        listname = 'markerlist'+i%3;
        panel = $(listname)
        if (locations[i].includes_main_post == 1) {
          var selectedicon = new GIcon(G_DEFAULT_ICON);
          selectedicon.image = "http://gmaps-samples.googlecode.com/svn/trunk/demo/bluemarker.png";
        } else if (locations[i].reply_count == 0) {
          var selectedicon = new GIcon(G_DEFAULT_ICON);
          selectedicon.image = "http://gmaps-samples.googlecode.com/svn/trunk/markers/green/blank.png";
        } else if (locations[i].retweet_count == 0) {
          var selectedicon = new GIcon(G_DEFAULT_ICON);
          selectedicon.image = "http://gmaps-samples.googlecode.com/svn/trunk/markers/orange/blank.png";
        } else {
          var selectedicon = new GIcon(G_DEFAULT_ICON);
          selectedicon.image = "http://gmaps-samples.googlecode.com/svn/trunk/markers/red/blank.png";
        }
        if (locations[i].reply_count == 1) {
          var replies = "reply";
        } else {
          var replies = "replies";
        }
        if (locations[i].retweet_count == 1) {
          var retweets = "retweet";
        } else {
          var retweets = "retweets";
        }
        var title = '<div style="float: right; padding-right:4px"><img src ="' + selectedicon.image + '">' +
        '</div><strong>' + locations[i].name + '</strong><br> (' + locations[i].reply_count + ' ' + replies +
        ' and ' + locations[i].retweet_count + ' ' + retweets + ')';
        // Code for side panel
        var item = document.createElement("div");
        var tit = document.createElement("div");
        tit.innerHTML = title;
        item.style.cssText = 'padding:2px 0;width:200px;max-height:52px;overflow:hidden;border-bottom:1px solid #E0ECFF;cursor:pointer;overflow:hidden;';
        panel.appendChild(item);
        item.appendChild(tit);
        GEvent.addDomListener(item, "mouseover", function() {
                                this.style.backgroundColor = "#E0ECFF";
                              });
        GEvent.addDomListener(item, "mouseout", function() {
                                this.style.backgroundColor = "white";
                              });
        var latlng = new GLatLng(locations[i].latitude, locations[i].longitude);
        var marker = new GMarker(latlng, {icon: selectedicon});
        var fn = markerClickFn(locations[i].name, locations[i].reply_count, locations[i].retweet_count,
                               locations[i].posts, replies, retweets, latlng);
        GEvent.addListener(marker, "click", fn);
        GEvent.addDomListener(tit, "click", fn);
        markers.push(marker);
      }
      markerClusterer = new MarkerClusterer(map, markers);
   }

   function markerClickFn(name, reply_count, retweet_count, posts, replies, retweets, latlng) {
     return function() {
     var infoHtml = '<div class="infoBlock">' + reply_count + ' ' + replies + ' and '
      + retweet_count + ' ' + retweets + ' from <strong>' + name + '</strong></div>';
     infoHtml += '<div class="enclosingBlock">' ;
     for (var i = 0; i < posts.length; i++) { 
         infoHtml += '<div class="infoBlock">' +
           '<img src="' + posts[i].author_avatar + '">' +
           '<div class="username">' + posts[i].author_username +'</div>' +
           '<div class="pub_date">' + posts[i].pub_date +'</div><br/>' +
           '<div class="post_text">' + posts[i].post_text +'</div>' +
           '</div>';
     }
     infoHtml += '</div>';
     map.openInfoWindowHtml(latlng, infoHtml);
     };
   }