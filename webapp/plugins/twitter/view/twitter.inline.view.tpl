<div class="">
  {if $description}
    {if $is_searchable}
        <a href="#" class="grid_search" title="Search" onclick="return false;">
        <img src="{$site_root_path}assets/img/search-icon.gif" id="grid_search_icon"></a>
    {/if}
     <i>{$description}</i>
     
  {/if}
</div>
    {if $error}
    <p class="error">
        {$error}
    </p>    
    {/if}
{if ($display eq 'tweets-all' and not $all_tweets) or 
    ($display eq 'tweets-mostreplies' and not $most_replied_to_tweets) or
    ($display eq 'tweets-mostretweeted' and not $most_retweeted) or
    ($display eq 'tweets-convo' and not $author_replies)}
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No tweets to display. <a href="{$site_root_path}crawler/run.php">Update your data now.</a>
    </p>
  </div>
{/if}
{if $all_tweets and ($display eq 'tweets-all' or $display eq 'tweets-questions')}
  {foreach from=$all_tweets key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
{/if}

{if $most_replied_to_tweets}
  {foreach from=$most_replied_to_tweets key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
{/if}

{if $most_retweeted}
  {foreach from=$most_retweeted key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
{/if}

{if $author_replies}
  {foreach from=$author_replies key=tahrt item=r name=foo}
    {include file="_post.qa.tpl" t=$t}
  {/foreach}
{/if}

{if ($display eq 'mentions-all' and not $all_mentions) or 
    ($display eq 'mentions-allreplies' and not $all_replies) or
    ($display eq 'mentions-orphan' and not $orphan_replies) or 
    ($display eq 'mentions-standalone' and not $standalone_replies)}
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No data to display. <a href="{$site_root_path}crawler/run.php">Update your data now.</a>
    </p>
  </div>
{/if}

{if $orphan_replies}
  {foreach from=$orphan_replies key=tid item=t name=foo}
    {include file="_post.otherorphan.tpl" t=$t}
  {/foreach}
  </form>
{/if} 

{if $all_mentions}
  {foreach from=$all_mentions key=tid item=t name=foo}
    {include file="_post.otherorphan.tpl" t=$t}
  {/foreach}
{/if}

{if $all_replies}
  {foreach from=$all_replies key=tid item=t name=foo}
    {include file="_post.other.tpl" t=$t}
  {/foreach}
{/if}

{if $standalone_replies}
  {foreach from=$standalone_replies key=tid item=t name=foo}
    {include file="_post.otherorphan.tpl" t=$t}
  {/foreach}
{/if}

{if ($display eq 'friends-mostactive' and not $people) or ($display eq 'friends-leastactive' and not $people) 
or ($display eq 'friends-mostfollowed' and not $people) or ($display eq 'friends-former' and not $people)
or ($display eq 'friends-notmutual' and not $people) 
or ($display eq 'followers-mostfollowed' and not $people) or ($display eq 'followers-leastlikely' and not $people)
or ($display eq 'followers-former' and not $people) or ($display eq 'followers-earliest' and not $people)}
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No users found. <a href="{$site_root_path}crawler/run.php">Update your data now.</a>
    </p>
  </div>
{/if}

{if $people}
  {foreach from=$people key=fid item=f name=foo}
    {include file="_user.tpl" t=$f}
  {/foreach}
{/if}

{if ($display eq 'links-friends' and not $links) or ($display eq 'links-favorites' and not $links) or ($display eq 'links-photos' and not $links)}
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;">
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No data to display. <a href="{$site_root_path}crawler/run.php">Update your data now.</a>
    </p>
  </div>
{/if}

{if $links}
  {foreach from=$links key=lid item=l name=foo}
    {include file="_link.tpl" t=$f}
  {/foreach}  
{/if}

<script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>

<script type="text/javascript">
  {literal}
  $(function() {
    // Begin reply assignment actions.
    $(".button").click(function() {
      var element = $(this);
      var Id = element.attr("id");
      var oid = Id;
      var pid = $("select#pid" + Id + " option:selected").val();
      var u = '{/literal}{$i->network_username|escape:'url'}{literal}';
      var t = 'inline.view.tpl';
      var ck = '{/literal}{$i->network_username|escape:'url'}-{$logged_in_user}-{$display}{literal}';
      var dataString = 'u=' + u + '&pid=' + pid + '&oid[]=' + oid + '&t=' + t + '&ck=' + ck;
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}post/mark-parent.php",
        data: dataString,
        success: function() {
          $('#div' + Id).html("<div class='success' id='message" + Id + "'></div>");
          $('#message' + Id).html("<p>Saved!</p>").hide().fadeIn(1500, function() {
            $('#message'+Id);  
          });
        }
      });
      return false;
    });
  });
  {/literal}
</script>

{if $is_searchable}
    {include file="_grid.search.tpl"}
    <script type="text/javascript" src="{$site_root_path}assets/js/grid_search.js"></script>
    
{/if}

