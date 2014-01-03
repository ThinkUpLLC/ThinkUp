{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div id="main" class="container">

    <div class="navbar">
        <span class="navbar-brand" style="margin-top: 12px;">Install ThinkUp:</span>
        <ul class="nav navbar-nav nav-pills pull-left">
            <li class="active"><a> <h4><i class="fa fa-tasks "></i> Check System Requirements</h4></a></li>
            <li><a class="disabled"> <h4><i class="fa fa-cogs"></i> Configure ThinkUp</h4></a></li>
            <li><a class="disabled"> <h4><i class="fa fa-lightbulb"></i> Finish</h4></a></li>
        </ul>
    </div>

    {if $requirements_met}
    
    
    <div class="row">
        <div class="col-md-3">

        </div>
        <div class="col-md-9">

        <div class="alert alert-success">
            <i class="fa fa-check"></i>
            <strong>Great!</strong> Your system has everything it needs to run ThinkUp.
        </div>        

        <a href="index.php?step=2" class="btn btn-large btn-success" id="nextstep">Let's Go <i class="fa fa-arrow-right"></i></a>
        
        </div>
    </div>
    
    {else}
    
    <div class="row">
        <div class="col-md-3">

        </div>
        <div class="col-md-9">
    
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i>
                <strong>Oops!</strong> Your web server isn't set up to run ThinkUp. Please fix the problems below and try installation again.
            </div>
            <div class="panel panel-default">
            <div class="panel-heading">ThinkUp System Requirements</div>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 20%">Component</th>
                        <th>Status<th>
                    <tr>
                <thead>
                <tbody>
                    <tr>
                        <th>PHP Version >= 5.2</td>
                        <td class="{if $php_compat}success{else}danger{/if}">{if $php_compat}Confirmed{else}ThinkUp needs PHP version greater or equal to v.{$php_required_version}{/if}</td>
                    </tr>
                    <tr>
                        <th>Data directory writable</td>
                        <td class="{if $permissions_compat}success{else}danger{/if}">{if $permissions_compat}Confirmed{else}ThinkUp's <code>data</code> directory, located at <code>{$writable_data_directory}</code>, must be writable for installation to complete. <a href="http://thinkup.com/docs/install/perms.html">Here's how to set that folder's permissions.</a>{/if}<td>
                    </tr>
                    <tr>
                        <th>Session directory writable</td>
                        <td class="{if $session_permissions_compat}success{else}danger{/if}">{if $session_permissions_compat}Confirmed{else}{if $writable_session_save_directory neq ''}The PHP <code>session.save_path</code> directory, located at <code>{$writable_session_save_directory}</code>, must be writable for installation to complete. <a href="http://php.net/manual/en/session.configuration.php#ini.session.save-path">Here's how to set that folder's permissions.</a>
              {else}The PHP <code>session.save_path</code> directory is not set. Please set it to a writable folder. <a href="http://php.net/manual/en/session.configuration.php#ini.session.save-path">Here's how to set that folder path and permissions.</a>{/if}{/if}</td>
                    </tr>
                    <tr>
                        <th>cURL Library</td>
                        <td class="{if $libs.curl}success{else}danger{/if}">{if $libs.curl}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/book.curl.php" target="_blank">cURL PHP library</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <th>GD Library</td>
                        <td class="{if $libs.gd}success{else}danger{/if}">{if $libs.gd}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/book.image.php" target="_blank">GD PHP library</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <th>PDO with MySQL Driver</td>
                        <td class="{if $libs.pdo AND $libs.pdo_mysql}success{else}danger{/if}">{if $libs.pdo AND $libs.pdo_mysql}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/pdo.installation.php" target="_blank">PDO extension</a> and the <a href="http://php.net/manual/en/ref.pdo-mysql.php" target="_blank">MySQL driver</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <th>JSON Library</td>
                        <td class="{if $libs.json}success{else}danger{/if}">{if $libs.json}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/book.json.php" target="_blank">JSON PHP extension</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <th>HASH Message Digest Framework</td>
                        <td class="{if $libs.hash}success{else}danger{/if}">{if $libs.hash}Confirmed{else}ThinkUp needs the <a href="http://php.net/manual/en/book.hash.php" target="_blank">HASH Message Digest Framework PHP extension</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <th>ZipArchive Support</td>
                        <td class="{if $libs.ZipArchive}success{else}danger{/if}">{if $libs.ZipArchive}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/class.ziparchive.php" target="_blank">ZipArchive PHP class</a> installed on your system.{/if}</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {/if}
        
</div>
  
{include file="_footer.tpl"}