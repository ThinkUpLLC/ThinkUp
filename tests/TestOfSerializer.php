<?php
/**
 *
 * ThinkUp/tests/TestOfSerializer.php
 *
 * Copyright (c) 2014 Gina Trapani
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
 * @copyright 2014 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfSerializer extends ThinkUpBasicUnitTestCase {
    public function testUnserializeString() {
        //Valid string
        $result = Serializer::unserializeString('O:1:"a":1:{s:5:"value";s:3:"100";}');
        $this->assertNotNull($result);
        $this->assertIsA($result, "Object");

        //Invalid string
        $this->expectException("SerializerException");
        $result = Serializer::unserializeString("{'Organization': 'ThinkUp Documentation Team'}");
        $this->assertNull($result);
    }
}
