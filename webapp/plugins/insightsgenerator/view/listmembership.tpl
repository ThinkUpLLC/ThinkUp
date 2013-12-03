{include file=$tpl_path|cat:'_header.tpl'}

{if  !$expand }
<div class="pull-right detail-btn"><button class="btn btn-info btn-xs" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-list"></i> <a href="?u={$i->instance->network_username}&amp;n={$i->instance->network}&amp;d={$i->date|date_format:'%Y-%m-%d'}&amp;s={$i->slug}">{$i->prefix}</a></span> 

<i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

{include file=$tpl_path|cat:'_counthistorychart.tpl'}

{include file=$tpl_path|cat:'_footer.tpl'}
