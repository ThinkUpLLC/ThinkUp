<?php
/**
 *
 * ThinkUp/webapp/plugins/GooglePlus/controller/class.GooglePlusPluginConfigurationController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie
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
 * GooglePlus Plugin Configuration Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

class GooglePlusPluginConfigurationController extends PluginConfigurationController {
    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.'plugins/googleplus/view/googleplus.account.index.tpl');
        $this->addToView('message',
            'Hello, world! This is the Google+ plugin configuration page for  '.$this->owner->email .'.');

        /** set option fields **/
        // name text field
        $name_field = array('name' => 'clientid', 'label' => 'Client ID'); // set an element name and label
        $name_field['default_value'] = ''; // set default value
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('clientid', 'A client ID is required to use Google+.');

        return $this->generateView();
    }
}
