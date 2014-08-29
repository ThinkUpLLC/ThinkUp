{foreach from=$i->related_data.changes item=change name=changed }
<div class="biotracker_change">
    {insert name="string_diff" from_text=$change.before to_text=$change.after assign="bio"}
    {include file=$tpl_path|cat:"_user.tpl" user=$change.user
      bio_diff=$bio bio_before=$change.before bio_after=$change.after}
</div>
{/foreach}

