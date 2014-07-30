{include file="_header.tpl" body_classes="settings account menu-off"}
{include file="_navigation.tpl"}

<div id="main" class="container">

    {if $requirements_met}


    <div class="">
        <div class="">

        <div class="alert alert-success">
            <i class="fa fa-check"></i>
            <strong>Great!</strong> Your system has everything it needs to run ThinkUp.
        </div>

        <a href="index.php?step=2" class="btn btn-large btn-success" id="nextstep">Let's Go <i class="fa fa-arrow-right"></i></a>

        </div>
    </div>

    {else}

    <div class="">
        <div class="">

            <header>
                <h1>ThinkUp System Requirements</h1>
            </header>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i>
                <strong>Oops!</strong> Your web server isn't set up to run ThinkUp. Please fix the problems below and try installation again.
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Status<td>
                    <tr>
                <thead>
                <tbody>
                    <tr>
                        <td>PHP Version >= 5.2</td>
                        <td class="{if $php_compat}{else}danger{/if}">{if $php_compat}<i class="fa fa-check-circle icon"></i> Confirmed{else}ThinkUp needs PHP version greater or equal to v.{$php_required_version}{/if}</td>
                    </tr>
                    <tr>
                        <td>Data directory writable</td>
                        <td class="{if $permissions_compat}{else}danger{/if}">{if $permissions_compat}<i class="fa fa-check-circle icon"></i> Confirmed{else}ThinkUp's <code>data</code> directory, located at <code>{$writable_data_directory}</code>, must be writable for installation to complete. <a href="http://thinkup.com/docs/install/perms.html">Here's how to set that folder's permissions.</a>{/if}<td>
                    </tr>
                    <tr>
                        <td>Session directory writable</td>
                        <td class="{if $session_permissions_compat}{else}danger{/if}">{if $session_permissions_compat}<i class="fa fa-check-circle icon"></i> Confirmed{else}{if $writable_session_save_directory neq ''}The PHP <code>session.save_path</code> directory, located at <code>{$writable_session_save_directory}</code>, must be writable for installation to complete. <a href="http://php.net/manual/en/session.configuration.php#ini.session.save-path">Here's how to set that folder's permissions.</a>
              {else}The PHP <code>session.save_path</code> directory is not set. Please set it to a writable folder. <a href="http://php.net/manual/en/session.configuration.php#ini.session.save-path">Here's how to set that folder path and permissions.</a>{/if}{/if}</td>
                    </tr>
                    <tr>
                        <td>cURL Library</td>
                        <td class="{if $libs.curl}{else}danger{/if}">{if $libs.curl}<i class="fa fa-check-circle icon"></i> Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/book.curl.php" target="_blank">cURL PHP library</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <td>GD Library</td>
                        <td class="{if $libs.gd}{else}danger{/if}">{if $libs.gd}<i class="fa fa-check-circle icon"></i> Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/book.image.php" target="_blank">GD PHP library</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <td>PDO with MySQL Driver</td>
                        <td class="{if $libs.pdo AND $libs.pdo_mysql}{else}danger{/if}">{if $libs.pdo AND $libs.pdo_mysql}<i class="fa fa-check-circle icon"></i> Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/pdo.installation.php" target="_blank">PDO extension</a> and the <a href="http://php.net/manual/en/ref.pdo-mysql.php" target="_blank">MySQL driver</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <td>JSON Library</td>
                        <td class="{if $libs.json}{else}danger{/if}">{if $libs.json}<i class="fa fa-check-circle icon"></i> Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/book.json.php" target="_blank">JSON PHP extension</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <td>HASH Message Digest Framework</td>
                        <td class="{if $libs.hash}{else}danger{/if}">{if $libs.hash}<i class="fa fa-check-circle icon"></i> Confirmed{else}ThinkUp needs the <a href="http://php.net/manual/en/book.hash.php" target="_blank">HASH Message Digest Framework PHP extension</a> installed on your system.{/if}</td>
                    </tr>
                    <tr>
                        <td>ZipArchive Support</td>
                        <td class="{if $libs.ZipArchive}{else}danger{/if}">{if $libs.ZipArchive}<i class="fa fa-check-circle icon"></i> Confirmed{else}ThinkUp needs the <a href="http://www.php.net/manual/en/class.ziparchive.php" target="_blank">ZipArchive PHP class</a> installed on your system.{/if}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {/if}

</div>

{include file="_footer.tpl"}