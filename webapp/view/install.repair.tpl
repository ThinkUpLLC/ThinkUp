{include file="_install.header.tpl"}
  <div id="installer-page" class="container_24 round-all">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        <h2 class="clearfix step_title">Repairing</h2>
        {include file="_usermessage.tpl"}
        {if $posted}
          {if $succeed}
          <div style="margin-bottom: 20px;">
            <p class="success"><strong>Repairs complete</strong>. Please remove <code>$THINKUP_CFG['repair'] = true;</code>
              from config.inc.php to prevent this page from being used by unauthorized users.
              {if $username && password}
                Your newly created admin user: <strong>{$username}</strong>, password:
                <strong>{$password}</strong>
              {/if}
            </p>
          </div>
          <div class="clearfix">
            {foreach from=$messages_db item=msg}
              {$msg}
            {/foreach}
            {foreach from=$messages_admin item=msg}
              {$msg}
            {/foreach}
          </div>
          {else}
          <div class="clearfix error_message">
            <strong>Ups!</strong> Something goes wrong, read the hints below!
          </div>
          <div class="clearfix">
            {foreach from=$messages_db item=msg}
              {$msg}
            {/foreach}
            {foreach from=$messages_admin item=msg}
              {$msg}
            {/foreach}
            {foreach from=$messages_error item=msg}
              {$msg}
            {/foreach}
          </div>
          {/if}
        {elseif $show_form}
        <form class="input" name="form1" method="post" action="{$action_form}">
          
          <div class="clearfix append_20">
            <div class="grid_10 prefix_7 left">
              <input type="submit" name="repair" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Repair &raquo">
            </div>
          </div>
        </form>
        {/if}
      </div>
    </div>
  </div>
</body>
</html>
{include file="_install.footer.tpl"}