{include file=$tpl_path|cat:'_header.tpl'}

{if  !$expand }
<<<<<<< HEAD
<div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-list"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->headline}</a></span> 
=======
<div class="pull-right detail-btn"><button class="btn btn-info btn-xs" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="fa fa-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="fa icon-white fa-list"></i> <a href="?u={$i->instance->network_username}&amp;n={$i->instance->network}&amp;d={$i->date|date_format:'%Y-%m-%d'}&amp;s={$i->slug}">{$i->prefix}</a></span> 
>>>>>>> 785d685... Update to Font Awesome 4 and replace Glyphicons markup throughout app.

<i class="fa fa-{$i->instance->network}{if $i->instance->network eq 'google+'} fa-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

{include file=$tpl_path|cat:'_counthistorychart.tpl'}

{include file=$tpl_path|cat:'_footer.tpl'}
