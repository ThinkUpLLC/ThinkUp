{include file=$tpl_path|cat:'_header.tpl'}

<<<<<<< HEAD
<span class="label label-info"><i class="icon-white icon-bullhorn"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->headline}</a></span> 
=======
<span class="label label-info"><i class="fa icon-white fa-bullhorn"></i> <a href="?u={$i->instance->network_username}&amp;n={$i->instance->network}&amp;d={$i->date|date_format:'%Y-%m-%d'}&amp;s={$i->slug}">{$i->prefix}</a></span> 
>>>>>>> 785d685... Update to Font Awesome 4 and replace Glyphicons markup throughout app.

<i class="fa fa-{$i->instance->network}{if $i->instance->network eq 'google+'} fa-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

<div class="insight-attachment-detail post">
    {include file=$tpl_path|cat:"_post.tpl" post=$i->related_data hide_insight_header=true}
</div>

{include file=$tpl_path|cat:'_footer.tpl'}