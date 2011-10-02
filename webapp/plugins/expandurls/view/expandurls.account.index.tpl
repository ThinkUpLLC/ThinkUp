<div class="append_20">
<h2 class="subhead">Expand URLs Plugin {insert name="help_link" id='expandurls'}</h2>

<p>Expands shortened links, including images.</p>

<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>


{if $options_markup}
<div {if $user_is_admin}style="border: solid gray 1px;padding:10px;margin:20px"{/if}>
{if $user_is_admin}
<h2 class="subhead">Set Up the ExpandURLs Plugin</h2>
{include file="_usermessage.tpl" field="setup"}
{/if}
{$options_markup}
</p>
</div>
{/if}

