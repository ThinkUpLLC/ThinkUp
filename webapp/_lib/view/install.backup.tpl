{include file="_header.tpl" body_classes="settings menu-open" body_id="settings-main"}
{include file="_navigation.tpl"}

<div class="container">


        <div class="section thinkup-canvas clearfix" id="backup_data">

        <div class="plugin-info">

            <span class="pull-right">{insert name="help_link" id='backup'}</span>
            <h2>
                <i class="fa fa-download text-muted"></i> Back up ThinkUp's entire database
            </h2>

        </div>

            {if $no_zip_support}
                <div class="alert alert-error">
                    <p>
                    <span class="fa fa-info-circle"></span>
                    It looks like your server setup does not support the
                    <code><a href="http://www.php.net/manual/en/book.zip.php">Zip</a></code> library that is required to complete a backup
                    through this interface.
                    <br /><br />
                    You can also try backing up your data using
                    <a href="http://www.thegeekstuff.com/2008/09/backup-and-restore-mysql-database-using-mysqldump/">mysqldump</a>.
                    </p>
                </div>
            {else}

                {if $high_table_row_count}
                    <!-- too many db records, use CLI interface? -->
                    <div class="alert">
                        <p>
                        <span class="fa fa-info-circle"></span>
                        Wow, your database has grown! The <b>{$high_table_row_count.table}</b> table has
                        <b>{$high_table_row_count.count|number_format:0:".":","} rows</b>.
                        Since backing up such a big database can time out in the browser, we recommend that you use the
                        <a href="http://thinkup.com/docs/install/backup.html"><b>command line backup tool</b></a> when backing up ThinkUp.
                        </p>
                    </div>
                {/if}

                <p class="help-block">
                If you have any issues using this backup feature, you can use <a href="http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html">mysqldump</a> to manually back up your ThinkUp data if you have access to your server.
                </p>

                <input type="button" id="login-save" name="Submit"
                onclick="document.location.href='?backup=true'"
                class="btn btn-primary btn-large" value="Backup Now">




                <h2 style="padding-top : 40px;"><i class="fa fa-upload-alt icon-muted"></i> Restore Your Thinkup Database</h2>

                <form name="backup_form" id="backup-form" class="form-horizontal" method="post" enctype="multipart/form-data" action="{$site_root_path}install/backup.php">



                    <div style="margin-top: 12px; margin-bottom: 12px; margin-right: 20px; float: left;">
                        <input type="file" name="backup_file" id="backup_file" />
                    </div>

                    <div style="margin-top: 12px; margin-bottom: 12px; margin-right: 20px; float: left;">
                        <input type="submit" id="upload-backup-submit" name="Submit"
                        class="btn btn-large btn-disabled" value="Upload Backup File">
                        <span class="icon-2x fa-spinner fa-spin" id="uploading-status" style="display: none;"></span>
                    </div>

                </form>

                <script type="text/javascript">
                    {literal}

                    (function(e){"use strict";e.fn.filestyle=function(t){if(typeof t=="object"||typeof t=="undefined"){var n={buttonText:"Choose file",textField:!0,icon:!1,classButton:"",classText:"",classIcon:"icon-folder-open"};return t=e.extend(n,t),this.each(function(){var n=e(this);n.data("filestyle",!0),n.css({position:"fixed",top:"-100px",left:"-100px"}).parent().addClass("form-search").append((t.textField?'<input type="text" class="'+t.classText+'" disabled size="40" /> ':"")+'<button type="button" class="btn '+t.classButton+'" >'+(t.icon?'<i class="'+t.classIcon+'"></i> ':"")+t.buttonText+"</button>"),n.change(function(){n.parent().children(":text").val(e(this).val())}),n.parent().children(":button").click(function(){n.click()})})}return this.each(function(){var n=e(this);n.data("filestyle")===!0&&t==="clear"?(n.parent().children(":text").val(""),n.val("")):window.console.error("Method filestyle not defined!")})}})(jQuery);

                    $('#backup_file').filestyle({
                        buttonText: 'Select backup file',
                        classButton: 'btn-primary btn-large',
                        textField: false,
                        icon: true,
                        classIcon: 'fa fa-upload icon-white'
                    });
                    $('#backup_file').click( function() { $('#upload-backup-submit').addClass('btn-primary'); } );
                    $('#backup-form').submit( function() { $('#uploading-status').show() } );
                    {/literal}
                </script>

            {/if}

</div>

</div>


{include file="_footer.tpl" linkify=0}
