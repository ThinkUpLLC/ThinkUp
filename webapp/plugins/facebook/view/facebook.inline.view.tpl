<div class="">
  {if $description}<i>{$description}</i>{/if}
    {if $is_searchable}
        <a href="#" class="grid_search" title="Search" onclick="return false;">
        <img src="{$site_root_path}assets/img/search-icon.gif" id="grid_search_icon"></a>
        {include file="_grid.search.tpl"}
    {/if}
</div>

{if ($display eq 'all_facebook_posts' and not $all_facebook_posts) or 
    ($display eq 'all_facebook_replies' and not $all_facebook_replies) }
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No Facebook posts to display.
    </p>
  </div>
{/if}

{if $all_facebook_posts and $display eq 'all_facebook_posts'}
  {foreach from=$all_facebook_posts key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
{/if}

{if $all_facebook_replies}
  {foreach from=$all_facebook_replies key=tid item=t name=foo}
    {include file="_post.other.tpl" t=$t}
  {/foreach}
{/if}

{if $most_replied_to_posts}
  {foreach from=$most_replied_to_posts key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
{/if}


{if ($display eq 'followers_mostfollowed' and not $facebook_users) or ($display eq 'friends_mostactive' and not $facebook_users) }
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No Facebook users found.
    </p>
  </div>
{/if}

{if $facebook_users}
  {foreach from=$facebook_users key=fid item=f name=foo}
    {include file="_user.tpl" t=$f}
  {/foreach}
{/if}

{if ($display eq 'links_from_friends' and not $links_from_friends)}
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;">
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No data to display.
    </p>
  </div>
{/if}

{if $links_from_friends}
  {foreach from=$links_from_friends key=lid item=l name=foo}
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

