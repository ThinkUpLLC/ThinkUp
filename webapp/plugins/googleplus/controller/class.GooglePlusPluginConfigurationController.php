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
    /**
     *
     * @var Owner
     */
    var $owner;
    /**
     * Constructor
     * @param Owner $owner
     * @return GooglePlusPluginConfigurationController
     */
    public function __construct($owner) {
        parent::__construct($owner, 'googleplus');
        $this->disableCaching();
        $this->owner = $owner;
    }

    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.'plugins/googleplus/view/googleplus.account.index.tpl');

        /** set option fields **/
        // client ID text field
        $name_field = array('name' => 'google_plus_client_id', 'label' => 'Client ID'); // set an element name and label
        $name_field['default_value'] = ''; // set default value
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('google_plus_client_id', 'A client ID is required to use Google+.');

        // client secret text field
        $name_field = array('name' => 'google_plus_client_secret', 'label' => 'Client secret');
        $name_field['default_value'] = ''; // set default value
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('google_plus_client_secret',
        'A client secret is required to use Google+.');

        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('googleplus', true); //get cached

        if (isset($options['google_plus_client_id']->option_value)
        && isset($options['google_plus_client_secret']->option_value)) {
            $this->setUpGPlusInteractions($options);
        } else {
            $this->addErrorMessage('Please set your Google+ client ID and secret.');
        }
        return $this->generateView();
    }

    /**
     * Add user auth link or process incoming auth requests.
     * @param array $options Plugin options array
     */
    protected function setUpGPlusInteractions(array $options) {
        // Create OAuth link
        $client_id = $options['google_plus_client_id']->option_value;
        //prep redirect URI
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $redirect_uri = urlencode('http://'.$_SERVER['SERVER_NAME'].$site_root_path.'account/?p=googleplus');

        $oauth_link = "https://accounts.google.com/o/oauth2/auth?client_id=".$client_id.
        "&redirect_uri=".$redirect_uri."&scope=https://www.googleapis.com/auth/plus.me&response_type=code";
        $this->addToView('oauth_link', $oauth_link);
    }
}
