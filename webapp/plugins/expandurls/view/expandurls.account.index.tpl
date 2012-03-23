<div class="append_20">

<div class="alert helpful">
    {insert name="help_link" id='expandurls'}
    <h2>Expand URLs Plugin</h2>
    <p>Expands shortened links, gathers link image thumbnails, and captures link clickthrough rates.</p><br>
    <p><strong>Important</strong>: To capture clickthrough rates, enter your Bitly API credentials in the Settings area below, and shorten URLs in your posts using Bitly.</p>
</div>

<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>
<br><br>

{if $options_markup}
{if $user_is_admin}
{include file="_plugin.showhider.tpl"}
{include file="_usermessage.tpl" field="setup"}
{/if}
{$options_markup}
</p>
</div>
{/if}
