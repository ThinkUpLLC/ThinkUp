{include file="_header.tpl" enable_bootstrap=1}
{include file="_statusbar.tpl" enable_bootstrap=1}

<div id="main" class="container">

    <div class="navbar">
        <div class="navbar-inner">
        <span class="brand" style="margin-top: 12px;">Install ThinkUp:</span>
        <ul class="nav pull-left">
            <li class="active"><a> <h4><i class="icon-tasks "></i> Check System Requirements</h4></a></li>
            <li><a class="disabled"> <h4><i class="icon-cogs"></i> Configure ThinkUp</h4></a></li>
            <li><a class="disabled"> <h4><i class="icon-lightbulb"></i> Finish</h4></a></li>
        </ul>
        </div>
    </div>

    {if $requirements_met}
    
    
    <div class="row">
        <div class="span3">

        </div>
        <div class="span9">

        <div class="alert alert-success">
            <i class="icon-ok-circle"></i>
            <strong>Great!</strong> Your system has everything it needs to run ThinkUp.
        </div>        

        <a href="index.php?step=2" class="btn btn-large btn-success" id="nextstep">Let's Go <i class="icon-arrow-right"></i></a>
        
        </div>
    </div>
    
    {else}
    
    <div class="row">
        <div class="span3">

        </div>
        <div class="span9">
    
            <div class="alert alert-error">
                <i class="icon-exclamation-sign"></i>
                <strong>Oops!</strong> Your web server isn't set up to run ThinkUp. Please fix the problems below and try installation again.
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 20%">Component</th>
                        <th>Status<th>
                    <tr>
                <thead>
                <tbody>
                    <tr class="{if $php_compat}success{else}error{/if}">
                        <th>PHP Version >= 5.2</td>
                        <td>{if $php_compat}Confirmed{else}ThinkUp needs PHP version greater or equal to v.{$php_required_version}{/if}</td>
                    </tr>
                    <tr class="{if $permissions_compat}success{else}error{/if}">
                        <th>Data directory writable</td>
                        <td>{if $permissions_compat}Confirmed{else}ThinkUp's <code>data</code> directory, located at <code>{$writable_data_directory}</code>, must be writable for installation to complete. <a href="http://thinkup.com/docs/install/perms.html">Here's how to set that folder's permissions.</a>{/if}<td>
                    </tr>
                    <tr class="{if $session_permissions_compat}success{else}error{/if}">
                        <th>Session directory writable</td>
                        <td>{if $session_permissions_compat}Confirmed{else}{if $writable_session_save_directory neq ''}The PHP <code>session.save_path</code> directory, located at <code>{$writable_session_save_directory}</code>, must be writable for installation to complete. <a href="http://php.net/manual/en/session.configuration.php#ini.session.save-path">Here's how to set that folder's permissions.</a>
              {else}The PHP <code>session.save_path</code> directory is not set. Please set it to a writable folder. <a href="http://php.net/manual/en/session.configuration.php#ini.session.save-path">Here's how to set that folder path and permissions.</a>{/if}{/if}</td>
                    </tr>
                    <tr class="{if $libs.curl}success{else}error{/if}">
                        <th>cURL Library</td>
                        <td>{if $libs.curl}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/book.curl.php" target="_blank">cURL PHP library</a> installed on your system.{/if}</td>
                    </tr>
                    <tr class="{if $libs.gd}success{else}error{/if}">
                        <th>GD Library</td>
                        <td>{if $libs.gd}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/book.image.php" target="_blank">GD PHP library</a> installed on your system.{/if}</td>
                    </tr>
                    <tr class="{if $libs.pdo AND $libs.pdo_mysql}success{else}error{/if}">
                        <th>PDO with MySQL Driver</td>
                        <td>{if $libs.pdo AND $libs.pdo_mysql}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/pdo.installation.php" target="_blank">PDO extension</a> and the <a href="http://php.net/manual/en/ref.pdo-mysql.php" target="_blank">MySQL driver</a> installed on your system.{/if}</td>
                    </tr>
                    <tr class="{if $libs.json}success{else}error{/if}">
                        <th>JSON Library</td>
                        <td>{if $libs.json}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/book.json.php" target="_blank">JSON PHP extension</a> installed on your system.{/if}</td>
                    </tr>
                    <tr class="{if $libs.hash}success{else}error{/if}">
                        <th>HASH Message Digest Framework</td>
                        <td>{if $libs.hash}Confirmed{else}ThinkUp needs the <a href="http://php.net/manual/en/book.hash.php" target="_blank">HASH Message Digest Framework PHP extension</a> installed on your system.{/if}</td>
                    </tr>
                    <tr class="{if $libs.ZipArchive}success{else}error{/if}">
                        <th>ZipArchive Support</td>
                        <td>{if $libs.ZipArchive}Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/class.ziparchive.php" target="_blank">ZipArchive PHP class</a> installed on your system.{/if}</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

    {/if}
        
</div>
  
{include file="_footer.tpl" enable_bootstrap=1}