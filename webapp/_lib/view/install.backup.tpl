{include file="_header.tpl"}
{include file="_statusbar.tpl"}
  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        {include file="_usermessage.tpl"}

<div class="help-container">{insert name="help_link" id='backup'}</div>
<h1>Back Up Your {$app_title} Data</h1>

{if $no_zip_support}
<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        It looks like your server setup does not support a library 
        ( <a href="http://www.php.net/manual/en/book.zip.php">Zip</a> ) that is required to complete a backup 
        through this interface.
        <br /><br />
        You can also try backing up your data using 
        <a href="http://www.thegeekstuff.com/2008/09/backup-and-restore-mysql-database-using-mysqldump/">mysqldump</a>.
    </p>
</div>
{else}
 
<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        Click on the button below to back up your {$app_title} database. This new {$app_title} feature is in testing now; 
        if it doesn't work, run a mysqldump manually on your {$app_title} server.
    </p>
</div>

<input type="button" id="login-save" name="Submit" style="margin: 20px 0px 0px 20px;"
onclick="document.location.href='?backup=true'" 
class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Backup Now">

<br /><br />
<p><a href="javascript: history.go(-1)">&larr;Back</a></p>

<br /><br />

<div class="prepend_20">
    <h1>Restore Your {$app_title} Database</h1>
</div>
  
<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        Import a {$app_title} database from file by uploading it below.</p>
    </p>
</div>


<form name="backup_form" id="backup-form" method="post" enctype="multipart/form-data" 
style="margin: 20px 0px 0px 20px;" action="{$site_root_path}install/backup.php">

    <div id="uploading-status" style="display: none;">
        <img src="{$site_root_path}assets/img/loading.gif" width="31" height="31" />
    </div>

    <label for="backup_file">
        Backup file:
    </label>
    &nbsp;
    <input type="file" name="backup_file" id="backup_file" />
    &nbsp;
    <input type="submit" id="upload-backup-submit" name="Submit" style="display: none;" 
    class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Upload Backup File">

</form>

<script type="text/javascript">
{literal}
    $('#backup_file').click( function() { $('#upload-backup-submit').show(); } );
    $('#backup-form').submit( function() { $('#uploading-status').show() } );
{/literal}
</script>

{/if}

</div>
</div>
</div>
{include file="_install.footer.tpl"}
