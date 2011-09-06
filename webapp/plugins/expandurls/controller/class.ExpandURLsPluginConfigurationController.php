<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ExpandURLsPluginConfigurationController.php
 *
 * Copyright (c) 2009-2011 Sam Rose
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
 * Expand URLs Plugin Configuration controller
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Sam Rose
 * @author Sam Rose
 */
class ExpandURLsPluginConfigurationController extends PluginConfigurationController {
    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH. 'plugins/expandurls/view/expandurls.account.index.tpl');

        $links_to_expand = array( 'name' => 'links_to_expand', 
            'label' => 'Links to expand per crawl', 'default_value' => 1500);

        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $links_to_expand);

        /** set option fields **/
        // API key text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'flickr_api_key',
            'label'=>'Flickr API key (<a href="http://www.flickr.com/services/api/keys/">Get it here</a>)')); // add element

        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'bitly_api_key', 
            'label'=>'Bit.ly API key (<a href="http://www.bit.ly">Get it here</a>'));

        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'bitly_login', 
            'label'=>'Bit.ly Username'));

        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'bitly_limit', 
            'label'=>'Number of Bit.ly clicks to update', 'default_value'=>500));

        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'hour_cap', 
            'label'=>'Hours between click updates', 'default_value'=>6));

        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'day_cap', 
            'label'=>'Days to keep checking click per link', 'default_value'=>30));

        return $this->generateView();
    }
}
