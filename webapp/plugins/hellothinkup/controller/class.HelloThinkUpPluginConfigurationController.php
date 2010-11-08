<?php
/**
 *
 * ThinkUp/webapp/plugins/hellothinkup/controller/class.HelloThinkUpPluginConfigurationController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie
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
 */
/**
 * HelloThinkUp Plugin Configuration Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

class HelloThinkUpPluginConfigurationController extends PluginConfigurationController {
    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.'plugins/hellothinkup/view/hellothinkup.account.index.tpl');
        $this->addToView('message',
            'Hello, world! This is the example plugin configuration page for  '.$this->owner->email .'.');

        /** set option fields **/
        // name text field
        $name_field = array('name' => 'testname', 'label' => 'Enter Your Name'); // set an element name and label
        $name_field['default_value'] = 'Think Tank User'; // set default value
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field); // add element
        // set testname header
        $this->addPluginOptionHeader('testname', 'User Info'); // add a header for an element
        // set a special required message
        $this->addPluginOptionRequiredMessage('testname',
            'Please enter a name, because we\'d really like to have one...');

        // gender radio field
        $gender_field = array('name' => 'testgender', 'label' => 'Select a Gender'); // set an element name and label
        $gender_field['values'] = array('Female' => 1, 'Male' => 2, 'Other' => 3);
        $gender_field['default_value'] = '3'; // set default value
        $this->addPluginOption(self::FORM_RADIO_ELEMENT, $gender_field); // add element

        // Birth Year Select
        $bday_field = array('name' => 'testbirthyear', 'label' => 'Select The Year You Were Born');
        $years = array();
        $i = 1900;
        while ($i <= 2010) {
            $years['Born in ' . $i] = $i;
            $i++;
        }
        $bday_field['values'] =  $years;
        $bday_field['default_value'] = '2005';
        $this->addPluginOption(self::FORM_SELECT_ELEMENT, $bday_field);

        // Enable registration stuff
        $reg_field = array('name' => 'testregopen', 'label' => 'Open Registration');
        $this->addPluginOptionHeader('testregopen', 'Registration Options');
        $reg_field['values'] = array('Open' => 1, 'Closed' => 0);
        $this->addPluginOption(self::FORM_RADIO_ELEMENT, $reg_field);

        // registration key
        $reg_key = array('name' => 'RegKey', 'validation_regex' => '^\d+$');
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $reg_key);
        $this->setPluginOptionNotRequired('RegKey');
        $this->addPluginOptionRequiredMessage('RegKey',
            'Please enter interger value for RegKey');
        return $this->generateView();

    }
}
