<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='hellothinkup'}</span>
    <h1>
        <i class="fa fa-puzzle-piece text-muted"></i>
        Hello ThinkUp
    </h1>
    
    <p>{$message}</p>

</div>

{if $user_is_admin}
{include file="_plugin.showhider.tpl"}

    {include file="_usermessage.tpl" field="setup"}

    {$options_markup}

</div>
{/if}