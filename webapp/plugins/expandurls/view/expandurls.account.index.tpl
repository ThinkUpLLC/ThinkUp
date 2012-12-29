<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='expandurls'}</span>
    <h1>
        <img src="{$site_root_path}plugins/expandurls/assets/img/plugin_icon.png" class="plugin-image">
        Expand URLs Plugin
    </h1>

    <p>Expands shortened links, gathers link image thumbnails, and captures link clickthrough rates.</p>
    <p><strong>Important</strong>: To capture clickthrough rates, enter your Bitly API credentials in the Settings area below, and shorten URLs in your posts using Bitly.</p>

</div>


<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>

{$options_markup}

{if $user_is_admin}
    {include file="_plugin.showhider.tpl"}
    {include file="_usermessage.tpl" field="setup"}
</div>
{/if}