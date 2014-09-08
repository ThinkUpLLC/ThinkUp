{literal}ThinkUpAppVersion = new function()  {
  var CONTENT_URL = '{/literal}{$checker_url}{literal}';
  var ROOT = 'thinkup_version';

  function requestContent( local ) {
    var script = document.createElement('script');
    // How you'd pass the current URL into the request
    // script.src = CONTENT_URL + '&url=' + escape(local || location.href);
    script.src = CONTENT_URL;
    // IE7 doesn't like this: document.body.appendChild(script);
    // Instead use:
    document.getElementsByTagName('head')[0].appendChild(script);
  }

  this.serverResponse = function( data ) {
    if (!data[0].version) {return;}
    var div = document.getElementById(ROOT);
    var txt = '';
//    console.debug(data);
//    console.debug('version ' + data[0].version);
    txt += ' <a  class="btn btn-success btn-xs upgrade-button" href="{/literal}{$site_root_path}{literal}install/upgrade-application.php" title="'+data[0].version+'">Upgrade ThinkUp</a>';
    div.innerHTML =  txt;  // assign new HTML into #ROOT
    div.style.display = 'inline'; // make element visible
  }

  document.write("<span id='" + ROOT + "' style='display: none'></span>");
  requestContent();
}{/literal}
