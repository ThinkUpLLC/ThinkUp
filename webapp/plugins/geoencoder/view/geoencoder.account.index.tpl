<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='geoencoder'}</span>
    <h1>
        <i class="fa fa-map-marker text-muted"></i>
        GeoEncoder
    </h1>

    <p>The GeoEncoder plugin plots a post's responses on a Google Map and can lists them by distance from the original poster.</p>

</div>


<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $options_markup}
{if $user_is_admin}
{include file="_plugin.showhider.tpl"}

{include file="_usermessage.tpl" field="setup"}
<p style="padding:5px">To set up the GeoEncoder plugin:</p>
<ol style="margin-left:40px">
<li><a href="http://code.google.com/apis/console#access" target="_blank" style="text-decoration : underline;">Create a project in the Google APIs Console.</a></li>
<li>Click "Services" and switch Google Maps API v2 to "On." </li>
<li>Click "API Access." Under "Simple API Access", copy and paste the Google-provided API key here.</li>
</ol>
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
{if $user_is_admin}
</div>
{/if}