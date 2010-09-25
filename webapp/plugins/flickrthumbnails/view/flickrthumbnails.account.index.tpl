<div class="append_20">
<h2 class="subhead">Flickr Thumbnails Configuration</h2>

<p>{$message}</p>

<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $options_markup}
<p>
{$options_markup}
<p>
{literal}
<script type="text/javascript">
if( required_values_set && ! is_admin) {
    $('#contact-admin-div').show();
}
{/literal}
</script>
{/if}

</div>
