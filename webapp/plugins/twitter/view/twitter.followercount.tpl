
 <div class="">
  {if $description}<i>{$description}</i>{/if}
</div>
    {if $error}
    <p class="error">
        {$error}
    </p>    
    {/if}

<br /><br />
<h2>By Day</h2>{if $historybyday.history|@count < 2}<i>Not enough data yet</i>{else}
<img src="http://chart.apis.google.com/chart?chs=800x200&chxt=x,y&chxl=0:|{foreach from=$historybyday.history key=tid item=t name=foo}{$t.date} ({$t.count|number_format})|{/foreach}1:||&cht=ls&chco=0077CC&chd=t:{foreach from=$historybyday.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,76A4FB,0,0,0">
{/if}

<br /><br />
<h2>By Week</h2>{if $historybyweek.history|@count < 2}<i>Not enough data yet</i>{else}
<img src="http://chart.apis.google.com/chart?chs=800x200&chxt=x,y&chxl=0:|{foreach from=$historybyweek.history key=tid item=t name=foo}{$t.date} ({$t.count|number_format})|{/foreach}1:||&cht=ls&chco=0077CC&chd=t:{foreach from=$historybyweek.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,76A4FB,0,0,0">
{/if}
<br /><br />
<h2>By Month</h2>{if $historybymonth.history|@count < 2}<i>Not enough data yet</i>{else}
<img src="http://chart.apis.google.com/chart?chs=800x200&chxt=x,y&chxl=0:|{foreach from=$historybymonth.history key=tid item=t name=foo}{$t.date}|{/foreach}1:||{foreach from=$historybymonth.history key=tid item=t name=foo}{$t.count|number_format}|{/foreach}&cht=ls&chco=0077CC&chd=t:{foreach from=$historybymonth.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}">
{/if}