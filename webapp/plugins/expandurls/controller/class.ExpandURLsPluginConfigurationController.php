<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ExpandURLsPluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Sam Rose
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
 * Expand URLs Plugin Configuration controller
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Sam Rose
 * @author Sam Rose
 */
class ExpandURLsPluginConfigurationController extends PluginConfigurationController {
    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH. 'plugins/expandurls/view/expandurls.account.index.tpl');
        $this->view_mgr->addHelp('expandurls', 'userguide/settings/plugins/expandurls');

        $links_to_expand = array( 'name' => 'links_to_expand', 'label' => 'Links to expand per crawl',
        'default_value' => 1500, 'size'=>4);

        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $links_to_expand);

        /** set option fields **/
        // API key text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'flickr_api_key', 'size'=>40,
        'label'=>'Flickr API key (<a href="http://www.flickr.com/services/api/keys/">Get it here</a>)')); // add element
        $this->setPluginOptionNotRequired('flickr_api_key');

        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'bitly_login',
        'label'=>'Bit.ly Username'));
        $this->setPluginOptionNotRequired('bitly_login');

        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'bitly_api_key', 'size'=>40,
        'label'=>'Bit.ly API key (<a href="http://bitly.com/a/your_api_key">Get it here</a>)'));
        $this->setPluginOptionNotRequired('bitly_api_key');

        $this->addToView('is_configured', true);

        return $this->generateView();
    }
}
