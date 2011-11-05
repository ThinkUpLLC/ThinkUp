<div class="append_20 alert helpful">

	{insert name="help_link" id='hellothinkup'}
	<h2 class="subhead">Hello ThinkUp Plugin</h2>
	
	<p>{$message}</p>
</div>

<div class="append_20">

{if $options_markup}
{if $user_is_admin}
{include file="_plugin.showhider.tpl"}

	{include file="_usermessage.tpl" field="setup"}
	
	{$options_markup}

{/if}{/if}

</div>
