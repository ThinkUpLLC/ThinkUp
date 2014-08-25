{foreach from=$i->related_data.changes item=change name=changed }
<div class="biotracker_change">
    <h5>{if $change.user->network eq 'twitter'}@{/if}{$change.user->username} changed their {$change.field_description}:</h5>
    <div class="text-diff">
    {$change.diff}
    </div>
    {* I dunno, maybe the user {include file=$tpl_path|cat:"_user.tpl" user=$change.user user_text=null} *}
</div>
{/foreach}

