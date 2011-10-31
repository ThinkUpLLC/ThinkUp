<div class="append_20">
<h2 class="subhead">Expand URLs Plugin {insert name="help_link" id='expandurls'}</h2>

<p>Expands shortened links, including images.</p>

<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>
<br><br>

{if $options_markup}
{if $user_is_admin}
{include file="_plugin.showhider.tpl"}
<h2 class="subhead">Settings</h2>
{include file="_usermessage.tpl" field="setup"}
{/if}
{$options_markup}
</p>
</div>
{/if}

