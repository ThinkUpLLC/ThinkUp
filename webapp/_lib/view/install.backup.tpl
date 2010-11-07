{include file="_install.header.tpl"}

<div class="container_24 thinkup-canvas round-all" style="padding: 0px 0px 30px 0px;">

  <div class="prepend_20">
    <h1>Backup Thinkup Database</h1>
    <input type="button" id="login-save" name="Submit" style="margin: 20px 0px 0px 200px;"
    onclick="document.location.href='?backup=true'"
    class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Backup Thinkup Database">
  </div>


  <div class="prepend_20">
    <h1>Restore Thinkup Database</h1>
  </div>
<form name="backup_form" id="backup-form" method="post" enctype="multipart/form-data" 
style="margin: 20px 0px 0px 200px;" action="{$site_root_path}install/backup.php">

<div id="uploading-status" style="display: none;">
    <img src="{$site_root_path}assets/img/loading.gif" width="31" height="31" />
</div>

{if isset($errormsg)}
<div class="ui-state-error ui-corner-all" 
style="margin: 20px 0px; padding: 0.5em 0.7em; width: 500px;" id="upload-error">
    <p>
    <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
    {$errormsg}
    </p>
</div>
{/if}

{if isset($successmsg)}
<div class="success" style="width: 500px">
   {$successmsg}
   </div>
{/if}
    <label for="backup_file">
    BackupFile:
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

</div>
{include file="_install.footer.tpl"}
