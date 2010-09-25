<div class="append_20">
<h2 class="subhead">GeoEncoder ThinkUp Plugin</h2>

<p>
    The GeoEncoder ThinkUp plugin geoencodes location data available in the database to point to a neghbourhood
    from where a particular post has been made.
</p>
<br>
<p>{$message}</p>

<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $options_markup}
<br />
{if $is_admin}
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
