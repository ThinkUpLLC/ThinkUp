/**
 *
 * ThinkUp/webapp/plugins/facebook/xd_receiver.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
*/
<?php 
// cache the xd_receiver
header('Cache-Control: max-age=225065900');
header('Expires:');
header('Pragma:');

?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>cross domain receiver page</title>
    </head>
    <body>
        <!--
        This is a cross domain (XD) receiver page. It needs to be placed on your domain so that the Javascript
        library can communicate within the iframe permission model. Put it here:
        http://www.example.com/xd_receiver.php
        -->
        <?php 
        echo '<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/XdCommReceiver.debug.js" type="text/javascript"></script>';
        ?>
    </body>
</html>
