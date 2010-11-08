{include file="_header.tpl"}
{include file="_statusbar.tpl"}
  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        {include file="_usermessage.tpl"}
        <br /><br />
        {if $needs_upgrade}
        <form method="POST">
        <input type="hidden" value="yes" name="upgrade" />
        <input type="submit" value="Upgrade ThinkUp's database now" />
        </form>
        
        <br /><br /><br />
        
        Note: Advanced users with more than a few hundred thousand posts in their  ThinkUp database should run this 
        update by hand and watch its progress.<br />To do so, run the following SQL commands in your ThinkUp database. 
        <br />(Be sure to replace tu_ with your ThinkUp table prefix.)
        <textarea cols="100" rows="10">
ALTER TABLE tu_posts CHANGE post_id post_id  bigint(20) UNSIGNED NOT NULL;
ALTER TABLE tu_posts CHANGE in_retweet_of_post_id in_retweet_of_post_id  bigint(20) UNSIGNED NULL;
ALTER TABLE tu_posts CHANGE in_reply_to_post_id in_reply_to_post_id bigint(20) UNSIGNED NULL;

ALTER TABLE tu_links CHANGE post_id post_id  bigint(20) UNSIGNED NOT NULL;

ALTER TABLE tu_post_errors CHANGE post_id post_id  bigint(20) UNSIGNED NOT NULL;

ALTER TABLE tu_users CHANGE last_post_id last_post_id  bigint(20) UNSIGNED NOT NULL;

ALTER TABLE tu_instances CHANGE last_status_id last_post_id  bigint(20) UNSIGNED NOT NULL;</textarea>
        {/if}
        
	      </div>
	    </div>
	  </div> <!-- end .thinkup-canvas -->

	{include file="_footer.tpl"}