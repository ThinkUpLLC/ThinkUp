<div class="append_20">
<h2 class="subhead">Expand URLs Plugin</h2>

<p>Expands shortened links, including images. <a href="http://thinkupapp.com/docs/userguide/settings/plugins/expandurls.html">Learn more.</a></p>

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
{/if}

<br/>