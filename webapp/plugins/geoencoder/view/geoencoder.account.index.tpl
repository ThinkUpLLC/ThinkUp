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
<br />
{if $user_is_admin}
<p>
	<b>Option(s)</b>
</p>
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
