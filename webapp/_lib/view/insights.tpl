
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
          </a>
          <div class="nav-collapse">

      {if $logged_in_user}            
<ul class="nav pull-right">           
		{if $user_is_admin}<li><script src="{$site_root_path}install/checkversion.php"></script></li>{/if}
        <li><p class="navbar-text">{$logged_in_user}{if $user_is_admin} (admin){/if}</p></li>
        <li><a href="{$site_root_path}account/?m=manage">Settings</a></li>
        <li><a href="{$site_root_path}session/logout.php">Log Out</a></li>
</ul>   
      {else}
<ul class="nav pull-right">         
        <li><a href="http://thinkupapp.com/" >Get ThinkUp</a></li>
        <li><a href="{$site_root_path}session/login.php" >Log In</a></li>

</ul>
      {/if}
          </div><!--/.nav-collapse -->
          <a href="{$site_root_path}{$logo_link}" class="brand pull-left"><span style="color : #00AEEF; font-weight : 800;">Think</span><span style="color : black; font-weight : 200;">Up</span></a>
          <a href="{$site_root_path}crawler/updatenow.php{if $developer_log}?log=full{/if}" class="btn pull-left">Update Data</a>

        </div>
      </div>
    </div>


    <div id="main" class="container">


{if sizeof($insights) eq 0}
<div class="alert urgent">
    <p>No insights are available! Get active on your network and check back later.</p>
</div>
{/if}
{assign var='cur_date' value=''}
{foreach from=$insights key=tid item=i name=foo}
    {if $i->text neq ''}
        {if $cur_date neq $i->date}
            <h1>{$i->date|relative_day|ucfirst}</h1>
            {assign var='cur_date' value=$i->date}
        {/if}
        <div class="alert {if $i->emphasis eq '2'}urgent{else}helpful{/if}">
            <p>
                <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
                {$i->text}
    
    <!--begin {$i->related_data_type} attachment data-->
                {if $i->related_data_type eq 'users'}
                    {include file="_insights.users.tpl"}
                {elseif $i->related_data_type eq 'post'}
                    {include file="_insights.post.tpl" post=$i->related_data}
                {elseif $i->related_data_type eq 'posts'}
                    {include file="_insights.posts.tpl"}
                {elseif $i->related_data_type eq 'count_history'}
                    {include file="_insights.count_history.tpl"}
                {/if}
    <!--end {$i->related_data_type} attachment data-->
    
               </span>
             </p>
        </div>
   {/if}
{/foreach}
  
<div class="view-all" id="older-posts-div">
  {if $next_page}
    <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}" id="next_page">&#60; Older</a>
  {/if}
  {if $last_page}
    | <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}" id="last_page">Newer &#62;</a>
  {/if}
</div>

  
