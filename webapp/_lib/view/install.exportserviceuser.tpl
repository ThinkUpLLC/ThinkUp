{include file="_header.tpl" body_classes="settings menu-open" body_id="settings-main"}
{include file="_navigation.tpl"}

<div class="container">

        <div class="section thinkup-canvas clearfix" id="export_service">

        <div class="plugin-info">
            <h2>
                <i class="fa fa-user text-muted"></i> Export a single user account's data
            </h2>

        </div>

        {if $no_zip_support}
        <div class="alert alert-error" >
            <p>
                <span class="fa fa-info-circle"></span>
                It looks like your server setup doesn't support the <code><a href="http://www.php.net/manual/en/book.zip.php">Zip</a></code> library that you'll need to create an export.
            </p>
        </div>
        {elseif $mysql_file_perms}
        <div class="alert alert-error">
            <p>
                <span class="fa fa-info-circle"></span>
                It looks like the MySQL user does not have the proper file permissions to export data. Please see the
                <a href="http://thinkup.com/docs/troubleshoot/messages/mysqlfile.html">ThinkUp
                documentation</a> for more info on how to resolve this issue.
            </p>
        </div>
        {elseif $grant_perms}
        <div class="alert alert-error">
            <p>
                <span class="fa fa-info-circle"></span>
                It looks like the MySQL user does not have the proper permissions to export data. Please see the
                <a href="http://thinkup.com/docs/troubleshoot/messages/mysqlgrant.html">ThinkUp
                documentation</a> for more info on how to resolve this issue.
            </p>
        </div>
        {else}
        <div class="">
            {if $messages}
            <div class="alert alert-error" style="margin-top: 10px; padding: 0.5em 0.7em;">
            <p>
                <span class="fa fa-info-circle" ></span>
                {foreach from=$messages key=mid item=m}
                {$m}<br />
                {/foreach}
                </p>
            </div>
            {else}
                <form method="post" class="form form-inline" action="{$site_root_path}install/exportuserdata.php">
                <select id="instance-select" name="instance_id">
                  {foreach from=$instances key=tid item=i}
                      <option value="{$i->id}">{$i->network_username} - {$i->network|capitalize} (updated {$i->crawler_last_run|relative_datetime} ago{if !$i->is_active} (paused){/if})</option>
                  {/foreach}
                </select>
                <input type="submit" class="btn btn-primary" value="Export User Data">
                </form>
                <br /><br />
            {/if}
        </div>
        {/if}

</div>

</div>


{include file="_footer.tpl" linkify=0}
