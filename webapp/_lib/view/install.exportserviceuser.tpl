
{include file="_header.tpl" enable_bootstrap=1}
{include file="_statusbar.tpl" enable_bootstrap=1}

<div class="container">

<div class="row">
    <div class="span3">
      <div id="tabs" class="embossed-block">
        <ul class="nav nav-tabs nav-stacked">

          <li><a href="{$site_root_path}account/?m=manage#plugins"><i class="icon icon-list-alt"></i> Plugins <i class="icon-chevron-right"></i></a></li>
          {if $user_is_admin}<li class="active"><a id="app-settings-tab" href="{$site_root_path}account/?m=manage#app_settings"><i class="icon icon-cogs"></i> Application <i class="icon-chevron-right"></i></a></li>{/if}
          <li><a href="{$site_root_path}account/?m=manage#instances"><i class="icon icon-lock"></i> Account <i class="icon-chevron-right"></i></a></li>
          {if $user_is_admin}<li><a href="{$site_root_path}account/?m=manage#ttusers"><i class="icon icon-group"></i> Users <i class="icon-chevron-right"></i></a></li>{/if}
        </ul>
      </div>
    </div><!--/span3-->
    <div class="span9">
        <div class="white-card">


        <div class="section thinkup-canvas clearfix" id="export_service">

        <a href="javascript: history.go(-1)" class="btn btn-mini"><i class="icon-chevron-left icon-muted"></i> Back</a>

        <div class="plugin-info">

            <span class="pull-right">{insert name="help_link" id='export_user_data'}</span>
            <h2>
                <i class="icon-user icon-muted"></i> Export a single user account's data
            </h2>

        </div>

        {include file="_usermessage.tpl" enable_bootstrap=1}

        {if $no_zip_support}
        <div class="alert alert-error" > 
            <p>
                <span class="icon-info-sign"></span>
                It looks like your server setup doesn't support the <code><a href="http://www.php.net/manual/en/book.zip.php">Zip</a></code> library that you'll need to create an export.
            </p>
        </div>
        {elseif $mysql_file_perms}
        <div class="alert alert-error"> 
            <p>
                <span class="icon-info-sign"></span>
                It looks like the MySQL user does not have the proper file permissions to export data. Please see the
                <a href="http://thinkup.com/docs/troubleshoot/messages/mysqlfile.html">ThinkUp 
                documentation</a> for more info on how to resolve this issue.
            </p>
        </div>
        {elseif $grant_perms}
        <div class="alert alert-error"> 
            <p>
                <span class="icon-info-sign"></span>
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
                <span class="icon-info-sign" ></span>
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

        </div> <!-- end #export_service -->


    </div>
</div>

</div>


{include file="_footer.tpl" linkify=0 enable_bootstrap=1}
