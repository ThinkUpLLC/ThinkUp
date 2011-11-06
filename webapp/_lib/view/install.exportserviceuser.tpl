{include file="_header.tpl"}
{include file="_statusbar.tpl"}
  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">

<div class="help-container">{insert name="help_link" id='export_user_data'}</div>
<h1>Export Service User Data</h1>

        {include file="_usermessage.tpl"}


{if $no_zip_support}
<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        It looks like your server setup does not support a library 
        ( <a href="http://www.php.net/manual/en/book.zip.php">Zip</a> ) that is required to complete an export 
        through this interface.
    </p>
</div>
{elseif $mysql_file_perms}
<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        It looks like the mysql user does not have the proper file permissions to Export Data. Please see the
        <a href="http://thinkup.readthedocs.org/en/latest/troubleshoot/common/backupcannotwrite.html">ThinkUp 
        documentation</a> for more info on how to resolve this issue.
    </p>
</div>
{elseif $grant_perms}
<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        It looks like the mysql user does not have the proper permissions to grant Export Access. Please see the
        <a href="http://thinkup.readthedocs.org/en/latest/troubleshoot/common/backupcannotwrite.html">ThinkUp 
        documentation</a> for more info on how to resolve this issue.
    </p>
</div>
{else}
    {if $messages}
    <div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        {foreach from=$messages key=mid item=m}
        {$m}<br />
        {/foreach}
        </p>
    </div>
    {else}
        <form method="post" action="{$site_root_path}install/exportuserdata.php">
        <select id="instance-select" name="instance_id">
          {foreach from=$instances key=tid item=i}
              <option value="{$i->id}">{$i->network_username} - {$i->network|capitalize} (updated {$i->crawler_last_run|relative_datetime} ago{if !$i->is_active} (paused){/if})</option>
          {/foreach}
        </select>
        <input type="submit" style="margin: 20px 0px 0px 20px;" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Export User Data">
        </form>
        <br /><br />
    {/if}
{/if}
<p><a href="javascript: history.go(-1)">&larr;Back</a></p>
</div>
</div>
</div>
{include file="_install.footer.tpl"}
