
 <div class="">
  {if $description}<i>{$description}</i>{/if}
</div>
    {if $error}
    <p class="error">
        {$error}
    </p>    
    {/if}

<h2>Follower Count By Day{if !$follower_count_history_by_day.history OR $follower_count_history_by_day.history|@count < 2}
<br /><i>Not enough data to display chart</i>
{else}
{if $follower_count_history_by_day.trend}({if $follower_count_history_by_day.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day){/if}</h2>
<img src="http://chart.apis.google.com/chart?chs=710x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$tid}{if $t eq "no data"}-nd{assign var="explain_nd" value="true"}{/if}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=007733&chd=t:{foreach from=$follower_count_history_by_day.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,cccccc,0,0,0" />
{if $explain_nd}<br><small>nd: No data available</small>{/if}
{if $follower_count_history_by_day.milestone}
<br /><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_day.milestone.will_take} days</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_day.milestone.next_milestone|number_format} followers</span> at this rate.</small>
{/if}
{/if}
<br /><br />

<h2>Follower Count By Week{if !$follower_count_history_by_week.history OR $follower_count_history_by_week.history|@count < 2}<br /><i>Not enough data to display chart</i><br clear="all"/>{else} {if $follower_count_history_by_week.trend != 0}({if $follower_count_history_by_week.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week){/if}</h2>
<img src="http://chart.apis.google.com/chart?chs=710x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{$tid}{if $t eq "no data"}-nd{assign var="explain_nd" value="true"}{/if}|{/foreach}1:|{foreach from=$follower_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=007733&chd=t:{foreach from=$follower_count_history_by_week.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,cccccc,0,0,0" />
{if $explain_nd}<br><small>nd: No data available</small>{/if}
{if $follower_count_history_by_week.milestone}
<br /><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.will_take} weeks</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.next_milestone|number_format} followers</span> at this rate.</small> 
{/if}
{/if}

<br /><br />

<h2>Follower Count By Month{if !$follower_count_history_by_month.history OR $follower_count_history_by_month.history|@count < 2}<br /><i>Not enough data to display chart</i><br clear="all"/>{else} {if $follower_count_history_by_month.trend != 0}({if $follower_count_history_by_month.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_month.trend|number_format}</span>/month){/if}</h2>
<img src="http://chart.apis.google.com/chart?chs=710x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_month.history key=tid item=t name=foo}{$tid}{if $t eq "no data"}-nd{assign var="explain_nd" value="true"}{/if}|{/foreach}1:|{foreach from=$follower_count_history_by_month.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=007733&chd=t:{foreach from=$follower_count_history_by_month.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,cccccc,0,0,0" />
{if $explain_nd}<br><small>nd: No data available</small>{/if}
{if $follower_count_history_by_month.milestone}
<br /><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.will_take} months</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.next_milestone|number_format} followers</span> at this rate.</small>
{/if}
{/if}
