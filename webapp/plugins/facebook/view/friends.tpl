<div class="section">
	<h2>{if $instance->network eq 'facebook page'}Likes{else}Friends{/if} By Day</h2>
	
	{if !$follower_count_history_by_day.history OR $follower_count_history_by_day.history|@count < 2}
	<div class="alert urgent">Not enough data to display chart</div>
	{else}
	{if $follower_count_history_by_day.trend}({if $follower_count_history_by_day.trend > 0}<div class="header"><span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day)</div>{/if}
	
	<div class="article">
	<img src="http://chart.apis.google.com/chart?chs=680x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_day.min_count},{$follower_count_history_by_day.max_count}&chxr={$follower_count_history_by_day.min_count},{$follower_count_history_by_day.max_count}&chxs=1N*s*&chm=N*s*,E5B9D4,0,-1,10,,e::5" />
	</div>
	
	{if $follower_count_history_by_day.milestone}
	<div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_day.milestone.will_take} day{if $follower_count_history_by_day.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_day.milestone.next_milestone|number_format} followers</span> at this rate.</small>
	</div>
	{/if}
	{/if}
</div>

<div class="section">
	<h2>{if $instance->network eq 'facebook page'}Likes{else}Friends{/if} By Week</h2>
	
	{if !$follower_count_history_by_week.history OR $follower_count_history_by_week.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
	{else} {if $follower_count_history_by_week.trend != 0}({if $follower_count_history_by_week.trend > 0}<div class="header"><span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week)</div>{/if}
	
	<div class="article">	
	<img src="http://chart.apis.google.com/chart?chs=680x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$follower_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_week.min_count},{$follower_count_history_by_week.max_count}&chxr={$follower_count_history_by_week.min_count},{$follower_count_history_by_week.max_count}&chxs=1N*s*&chm=N*s*,E5B9D4,0,-1,10,,e::5" />
	</div>
	
	{if $follower_count_history_by_week.milestone}
	<div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.will_take} week{if $follower_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.next_milestone|number_format} followers</span> at this rate.</small> 
	</div>
	{/if}
	{/if}
</div>

<div class="section">
	<h2>{if $instance->network eq 'facebook page'}Likes{else}Friends{/if} By Month</h2>
	
	{if !$follower_count_history_by_month.history OR $follower_count_history_by_month.history|@count < 2}<div class="alert urgent">Not enough data to display chart</i></div>
	{else} {if $follower_count_history_by_month.trend != 0}({if $follower_count_history_by_month.trend > 0}<div class="header"><span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_month.trend|number_format}</span>/month)</div>{/if}
	
	<div class="article">		
	<img src="http://chart.apis.google.com/chart?chs=680x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_month.history key=tid item=t name=foo}{$tid|date_format:"%b '%y"}|{/foreach}1:|{foreach from=$follower_count_history_by_month.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$follower_count_history_by_month.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_month.min_count},{$follower_count_history_by_month.max_count}&chxr={$follower_count_history_by_month.min_count},{$follower_count_history_by_month.max_count}&chxs=1N*s*&chm=N*s*,E5B9D4,0,-1,10,,e::5" />
	</div>
	
	
	{if $follower_count_history_by_month.milestone}
	<div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.will_take} month{if $follower_count_history_by_month.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.next_milestone|number_format} followers</span> at this rate.</small>
	</div>
	{/if}
	{/if}
</div>
