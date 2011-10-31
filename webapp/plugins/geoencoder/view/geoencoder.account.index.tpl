
<div class="append_20">

<h2 class="subhead">GeoEncoder Plugin {insert name="help_link" id='geoencoder'}</h2>

<p>
The GeoEncoder plugin plots a post's responses on a Google Map and can lists them by distance from the original poster.
</p>
<br>

<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $options_markup}
{if $user_is_admin}
{include file="_plugin.showhider.tpl"}
<h2 class="subhead">Settings</h2>
{include file="_usermessage.tpl" field="setup"}
<p style="padding:5px">To set up the GeoEncoder plugin:</p>
<ol style="margin-left:40px"><li><a href="http://code.google.com/apis/maps/signup.html" target="_blank">Sign up for a Google Maps API key</a>.</li>
<li>Set the web site URL to <br>
<code style="font-family:Courier;" id="clippy_2989">http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{$site_root_path}</code>
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
              width="100"
              height="14"
              class="clippy"
              id="clippy" >
      <param name="movie" value="{$site_root_path}assets/flash/clippy.swf"/>
      <param name="allowScriptAccess" value="always" />
      <param name="quality" value="high" />
      <param name="scale" value="noscale" />
      <param NAME="FlashVars" value="id=clippy_2989&amp;copied=copied!&amp;copyto=copy to clipboard">
      <param name="bgcolor" value="#FFFFFF">
      <param name="wmode" value="opaque">
      <embed src="{$site_root_path}assets/flash/clippy.swf"
             width="100"
             height="14"
             name="clippy"
             quality="high"
             allowScriptAccess="always"
             type="application/x-shockwave-flash"
             pluginspage="http://www.macromedia.com/go/getflashplayer"
             FlashVars="id=clippy_2989&amp;copied=copied!&amp;copyto=copy to clipboard"
             bgcolor="#FFFFFF"
             wmode="opaque"
      />
    </object>

</li>
<li>Enter the Google-provided API key here.</li></ol>
{/if}
<p>
{$options_markup}
<p>
{literal}
<script type="text/javascript">
if( ! required_values_set && ! is_admin) {
    $('#contact-admin-div').show();
}
{/literal}
</script>
{/if}
</div>
<br>
