{include file=$tpl_path|cat:'_header.tpl'}
<div class="pull-right detail-btn"><button class="btn btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal"></i></button></div>

<span class="label label-{if $i->emphasis eq '1'}inverse{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-list"></i> {$i->prefix}</span> 

{$i->text|link_usernames_to_twitter}

{include file=$tpl_path|cat:'_counthistorychart.tpl'}

{include file=$tpl_path|cat:'_footer.tpl'}