ThinkUp{$post_id} = new function() {literal} {
  var BASE_URL = {/literal}'http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{$site_root_path}';{literal}
  var STYLESHEET = BASE_URL + "assets/css/thinkupembedthread.css"
  var CONTENT_URL = BASE_URL + 'api/embed/v1/thread_js.php?p={/literal}{$post_id}&n={$network|urlencode}';
  var ROOT = 'thinkup_embed_{$post_id}{literal}';

  function requestStylesheet(stylesheet_url) {
    stylesheet = document.createElement("link");
    stylesheet.rel = "stylesheet";
    stylesheet.type = "text/css";
    stylesheet.href = stylesheet_url;
    stylesheet.media = "all";
    document.lastChild.firstChild.appendChild(stylesheet);
  }

  function requestContent( local ) {
    var script = document.createElement('script');
    // How you'd pass the current URL into the request
    // script.src = CONTENT_URL + '&amp;url=' + escape(local || location.href);
    script.src = CONTENT_URL;
    // IE7 doesn't like this: document.body.appendChild(script);
    // Instead use:
    document.getElementsByTagName('head')[0].appendChild(script);
  }

  this.serverResponse = function( data ) {
    if (!data[0].status) {return;}
    var div = document.getElementById(ROOT);
    var txt = '';
    //console.debug(data);
    //console.debug('status ' + data[0].status);
    if ( data[0].status == 'failed') {
        if ( data[0].message ) {txt = '<div class="thinkup_error">Error: ' + data[0].message + '</div>';}
    } else {
    //    console.debug('post ' + data[0].post);
    //    console.debug('replies ' + data[0].replies[1].text);
        txt += '<div style="float:left">';
        if (data[0].author_link != 'null') {
          txt += '<a href="'+data[0].author_link+'">';
        }
        txt += '<img src="' + data[0].author_avatar + '" class="thinkup_avatar"/>';
        if (data[0].author_link != 'null') {
          txt += '</a">';
        }
        txt += '</div><div class="thinkup_post">';
        if (data[0].post_link != 'null') {
          txt += '<a href="'+data[0].post_link+'">';
        }
        txt += data[0].author;
        if (data[0].author_link != 'null') {
          txt += '</a>';
        }
        txt += ': ' + data[0].post +
        '</div><br clear="all">';
        for (var i = 0; i < data[0].replies.length; i++) {
          if (txt.length > 0) { txt += " "; }
          txt += '<div class="thinkup_post_reply"><div style="float:left">'
          if (data[0].replies[i].author_link != 'null') {
            txt += '<a href="'+data[0].replies[i].author_link+'">';
          }
          txt += '<img src="' +
          data[0].replies[i].author_avatar + '"  class="thinkup_avatar"/>';
          if (data[0].replies[i].author_link != 'null') {
            txt += '</a>';
          }
          txt += '</div>';
          if (data[0].replies[i].post_link != 'null') {
            txt += '<a href="' + data[0].replies[i].post_link + '">';
          }
          txt += data[0].replies[i].author;
          if (data[0].replies[i].post_link != 'null') {
            txt += '</a>';
          }
          txt += ": " + data[0].replies[i].text + '</div><br clear="all">';
        }
    }
    txt += 'Powered by <a href="http://thinkup.com">ThinkUp</a>';
    div.innerHTML =  txt;  // assign new HTML into #ROOT
    div.style.display = 'block'; // make element visible
  }

  requestStylesheet(STYLESHEET);
  document.write("<div id='" + ROOT + "' style='display: none'></div>");
  requestContent();
}
{/literal}