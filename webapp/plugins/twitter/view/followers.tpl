{if $leastlikely|@count >1}
<div class="section">
    <h2>Most Discerning</h2>
    {foreach from=$leastlikely key=tid item=u name=foo}
      <div class="avatar-container" style="float:left;margin:7px;">
        <a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a>
      </div>
    {/foreach}
    <div class="view-all"><a href="index.php?v=followers-leastlikely&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $popular|@count >1}
<div class="section">
    <h2>Most Popular</h2>
    {foreach from=$popular key=tid item=u name=foo}
      <div class="avatar-container" style="float:left;margin:7px;">
        <a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a>
      </div>
    {/foreach}
    <div class="view-all"><a href="index.php?v=followers-mostfollowed&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}


<div class="section">
	<h2>Follower Count By Day</h2>
	
	{if !$follower_count_history_by_day.history OR $follower_count_history_by_day.history|@count < 2}
	<div class="alert urgent">Not enough data to display chart</div>
	{else}
	{if $follower_count_history_by_day.trend}({if $follower_count_history_by_day.trend > 0}<div class="article"><h3><span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day)</h3>{/if}
	<img src="http://chart.apis.google.com/chart?chs=710x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_day.min_count},{$follower_count_history_by_day.max_count}&chxr={$follower_count_history_by_day.min_count},{$follower_count_history_by_day.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />
	</div>
	{if $follower_count_history_by_day.milestone}
	<div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_day.milestone.will_take} day{if $follower_count_history_by_day.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_day.milestone.next_milestone|number_format} followers</span> at this rate.</small>
	</div>
	{/if}
	{/if}
</div>

<div class="section">
	<h2>Follower Count By Week</h2>
	
	{if !$follower_count_history_by_week.history OR $follower_count_history_by_week.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
	{else} {if $follower_count_history_by_week.trend != 0}({if $follower_count_history_by_week.trend > 0}<div class="article"><h3><span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week)</h3>{/if}
	<img src="http://chart.apis.google.com/chart?chs=710x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$follower_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_week.min_count},{$follower_count_history_by_week.max_count}&chxr={$follower_count_history_by_week.min_count},{$follower_count_history_by_week.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />
	</div>
	{if $follower_count_history_by_week.milestone}
	<div class="stream-pagination">
	<small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.will_take} week{if $follower_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.next_milestone|number_format} followers</span> at this rate.</small> 
	</div>
	{/if}
	{/if}
</div>

<div class="section">
	<h2>Follower Count By Month</h2>
	
	{if !$follower_count_history_by_month.history OR $follower_count_history_by_month.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
	{else}
	{if $follower_count_history_by_month.trend != 0}({if $follower_count_history_by_month.trend > 0}<div class="article"><h3><span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_month.trend|number_format}</span>/month)</h3>{/if}
	<img src="http://chart.apis.google.com/chart?chs=710x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_month.history key=tid item=t name=foo}{$tid|date_format:"%b '%y"}|{/foreach}1:|{foreach from=$follower_count_history_by_month.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$follower_count_history_by_month.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_month.min_count},{$follower_count_history_by_month.max_count}&chxr={$follower_count_history_by_month.min_count},{$follower_count_history_by_month.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />
	</div>
	{if $follower_count_history_by_month.milestone}
	<div class="stream-pagination">
	<small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.will_take} month{if $follower_count_history_by_month.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.next_milestone|number_format} followers</span> at this rate.</small>
	</div>
	{/if}
	{/if}
</div>

<div class="section">
	<h2>List Membership Count By Day</h2>
	{if !$list_membership_count_history_by_day.history OR $list_membership_count_history_by_day.history|@count < 2}
	<div class="alert urgent">Not enough data to display chart</div>
	{else}
	
	{if $list_membership_count_history_by_day.trend}({if $list_membership_count_history_by_day.trend > 0}<div class="article"><h3><span style="color:green">+{else}<span style="color:red">{/if}{$list_membership_count_history_by_day.trend|number_format}</span>/day)</h3>{/if}
	<img src="http://chart.apis.google.com/chart?chs=710x200&chxt=x,y&chxl=0:|{foreach from=$list_membership_count_history_by_day.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$list_membership_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$list_membership_count_history_by_day.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$list_membership_count_history_by_day.min_count},{$list_membership_count_history_by_day.max_count}&chxr={$list_membership_count_history_by_day.min_count},{$list_membership_count_history_by_day.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />
	</div>
	
	{if $list_membership_count_history_by_day.milestone}
	<div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_day.milestone.will_take} day{if $list_membership_count_history_by_day.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_day.milestone.next_milestone|number_format} groups</span> at this rate.</small>
	</div>
	{/if}
	{/if}
</div>

<div class="section">
	<h2>List Membership Count By Week</h2>
	{if !$list_membership_count_history_by_week.history OR $list_membership_count_history_by_week.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
	{else} {if $list_membership_count_history_by_week.trend != 0}({if $list_membership_count_history_by_week.trend > 0}<div class="article"><h3><span style="color:green">+{else}<span style="color:red">{/if}{$list_membership_count_history_by_week.trend|number_format}</span>/week)</h3>{/if}
	<img src="http://chart.apis.google.com/chart?chs=710x200&chxt=x,y&chxl=0:|{foreach from=$list_membership_count_history_by_week.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$list_membership_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$list_membership_count_history_by_week.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$list_membership_count_history_by_week.min_count},{$list_membership_count_history_by_week.max_count}&chxr={$list_membership_count_history_by_week.min_count},{$list_membership_count_history_by_week.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />
	</div>
	
	{if $list_membership_count_history_by_week.milestone}
	<div class="stream-pagination">
	<small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_week.milestone.will_take} week{if $list_membership_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_week.milestone.next_milestone|number_format} groups</span> at this rate.</small>
	</div>
	{/if}
	{/if}
	
	<br /><br />
</div>

<div class="section">
	<h2>List Membership Count By Month</h2>
	
	{if !$list_membership_count_history_by_month.history OR $list_membership_count_history_by_month.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
	{else} {if $list_membership_count_history_by_month.trend != 0}({if $list_membership_count_history_by_month.trend > 0}<div class="article"><h3><span style="color:green">+{else}<span style="color:red">{/if}{$list_membership_count_history_by_month.trend|number_format}</span>/month)</h3>{/if}
	<img src="http://chart.apis.google.com/chart?chs=710x200&chxt=x,y&chxl=0:|{foreach from=$list_membership_count_history_by_month.history key=tid item=t name=foo}{$tid|date_format:"%b '%y"}|{/foreach}1:|{foreach from=$list_membership_count_history_by_month.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$list_membership_count_history_by_month.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$list_membership_count_history_by_month.min_count},{$list_membership_count_history_by_month.max_count}&chxr={$list_membership_count_history_by_month.min_count},{$list_membership_count_history_by_month.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />
	</div>
	
	{if $list_membership_count_history_by_month.milestone}
	<div class="stream-pagination">
	<small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_month.milestone.will_take} month{if $list_membership_count_history_by_month.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_month.milestone.next_milestone|number_format} groups</span> at this rate.</small>
	</div>
	{/if}
	{/if}
</div>
