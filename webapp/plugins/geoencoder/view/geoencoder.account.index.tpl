<div class="append_20">
<h2 class="subhead">GeoEncoder Plugin</h2>

<p>
    The Geoencoder plugin fetches latitude and longitude points for a post or user's location to plot on a Google Map.
</p>
<br>

<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $options_markup}
<div {if $user_is_admin}style="border: solid gray 1px;padding:10px;margin:20px"{/if}>
<!-- Configure the Geoencoder Plugin -->
{if $user_is_admin}<h2 class="subhead">Configure the Geoencoder Plugin</h2>
<ol style="margin-left:40px"><li><a href="http://code.google.com/apis/maps/signup.html">Register your ThinkUp application with Google</a>.</li>
<li>Set the website URL to <pre>http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{$site_root_path}</pre></li>
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

<br/>
