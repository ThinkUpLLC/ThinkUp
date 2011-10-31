<div class="append_20">
<h2 class="subhead">Hello ThinkUp Plugin {insert name="help_link" id='hellothinkup'}</h2>

<p>{$message}</p>
<br><br>
{if $options_markup}
{if $user_is_admin}
{include file="_plugin.showhider.tpl"}
<h2 class="subhead">Plugin Settings Proof-of-Concept</h2>

{include file="_usermessage.tpl" field="setup"}

{$options_markup}
</div></div>

{/if}{/if}

