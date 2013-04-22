<?php
/**
 *
 * ThinkUp/webapp/install/setmode.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 */
session_start();
if (strtolower($_GET['m']) == "tests") {
    putenv("MODE=TESTS");
    $_SESSION["MODE"] = "TESTS";
    echo "Set to tests mode";
} elseif (strtolower($_GET['m']) == "prod") {
    putenv("MODE=PROD");
    $_SESSION["MODE"] = "PROD";
    echo "Set to prod mode";
} else {
    echo "Currently in ";
    if (isset($_SESSION["MODE"])) {
        echo strtolower($_SESSION["MODE"]);
    } else {
        echo " prod ";
    }
    echo " mode";
}

if (isset($_GET['rd'])) {
    putenv("RD_MODE=1");
    $_SESSION["RD_MODE"] = "1";
}
