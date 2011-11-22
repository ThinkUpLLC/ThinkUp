{include file="_header.tpl"}
{include file="_statusbar.tpl"}
  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20 alert stats">
      <div class="grid_22 push_1 clearfix">
<p><a href="javascript: history.go(-1)" class="linkbutton">&larr;Back</a></p>

{insert name="help_link" id='export_user_data'}
<h1>Export Service User Data</h1>

        {include file="_usermessage.tpl"}


{if $no_zip_support}
<div class="alert urgent" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        It looks like your server setup does not support a library 
        ( <a href="http://www.php.net/manual/en/book.zip.php">Zip</a> ) that is required to complete an export 
        through this interface.
    </p>
</div>
{elseif $mysql_file_perms}
<div class="alert urgent" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        It looks like the MySQL user does not have the proper file permissions to export data. Please see the
        <a href="http://thinkupapp.com/docs/troubleshoot/messages/mysqlfile.html">ThinkUp 
        documentation</a> for more info on how to resolve this issue.
    </p>
</div>
{elseif $grant_perms}
<div class="alert urgent" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        It looks like the MySQL user does not have the proper permissions to export data. Please see the
        <a href="http://thinkupapp.com/docs/troubleshoot/messages/mysqlgrant.html">ThinkUp 
        documentation</a> for more info on how to resolve this issue.
    </p>
</div>
{else}
<div class="">
    {if $messages}
    <div class="alert urgent" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
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
        <input type="submit" style="margin: 20px 0px 0px 20px;" class="linkbutton emphasized" value="Export User Data">
        </form>
        <br /><br />
    {/if}
</div>
{/if}

</div>
</div>
</div>
{include file="_install.footer.tpl"}
