<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='expandurls'}</span>
    <h1>
        <img src="{$site_root_path}plugins/expandurls/assets/img/plugin_icon.png" class="plugin-image">
        <i class="fa fa-link text-muted"></i>
        Expand URLs
    </h1>

    <p>Expands shortened links, gathers link image thumbnails, and captures link clickthrough rates.</p>
    <p><strong>Important</strong>: To capture clickthrough rates, enter your Bitly API credentials in the Settings area below, and shorten URLs in your posts using Bitly.</p>

</div>


<div id="contact-admin-div" style="display: none; margin-top: 20px;">
{include file="_plugin.admin-request.tpl"}
</div>



{if $user_is_admin}
    {include file="_usermessage.tpl" field="setup"}
    {include file="_plugin.showhider.tpl"}
{/if}

{if $options_markup}
<p>
{$options_markup}
</p>
{/if}

{if $user_is_admin}</div>{/if}