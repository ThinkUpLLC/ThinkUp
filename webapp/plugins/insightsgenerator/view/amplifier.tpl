{include file=$tpl_path|cat:'_header.tpl'}

<span class="label label-info"><i class="icon-white icon-star"></i> {$i->prefix}</span> 

{$i->text|link_usernames_to_twitter}

<div class="insight-attachment-detail post">
    {include file=$tpl_path|cat:"_post.tpl" post=$i->related_data hide_insight_header=true}
</div>

{include file=$tpl_path|cat:'_footer.tpl'}