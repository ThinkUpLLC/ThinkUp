<?php
/**
 *
 * ThinkUp/tests/all_tests.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2009-2011 Gina Trapani
 */
include 'init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/mock_objects.php';

$RUNNING_ALL_TESTS = true;
$TOTAL_PASSES = 0;
$TOTAL_FAILURES = 0;
$start_time = microtime(true);

require_once THINKUP_ROOT_PATH.'tests/all_model_tests.php';

require_once THINKUP_ROOT_PATH.'tests/all_plugin_tests.php';

require_once THINKUP_ROOT_PATH.'tests/all_integration_tests.php';

require_once THINKUP_ROOT_PATH.'tests/all_install_tests.php';

require_once THINKUP_ROOT_PATH.'tests/all_controller_tests.php';

$end_time = microtime(true);
$total_time = ($end_time - $start_time) / 60;

echo "
Total ThinkUp test passes: ".$TOTAL_PASSES."
Total ThinkUp test failures: ".$TOTAL_FAILURES."
Time elapsed: ".round($total_time)." minute(s)

";

echo trim(exec("cd ".THINKUP_ROOT_PATH."docs/source/; wc -w `find ./ -type f -name \*.rst` | tail -n 1")) .
" words of application documentation

";