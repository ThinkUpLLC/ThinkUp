<?php
/**
 *
 * ThinkUp/webapp/plugins/geoencoder/controller/class.GeoEncoderPluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Ekansh Preet Singh, Mark Wilkie
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
 * GeoEncoder Plugin configuration controller
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Ekansh Preet Singh, Mark Wilkie
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */

class GeoEncoderPluginConfigurationController extends PluginConfigurationController {

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/geoencoder/view/geoencoder.account.index.tpl');
        $this->view_mgr->addHelp('geoencoder', 'userguide/settings/plugins/geoencoder');
        $this->addToView('message', 'This is the GeoEncoder plugin configuration page for '.$this->owner->email .'.');

        /** set option fields **/
        // gmaps_api_key text field
        $name_field = array('name' => 'gmaps_api_key', 'label' => 'Google Maps API Key', 'size' => 55);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field);
        $this->addPluginOptionRequiredMessage('gmaps_api_key',
            'Please enter your Google Maps API Key');

        // distance_unit radio field
        $distance_unit_field = array('name' => 'distance_unit', 'label' => 'Unit of Distance');
        $distance_unit_field['values'] = array('Kilometers' => 'km', 'Miles' => 'mi');
        $distance_unit_field['default_value'] = 'km';
        $this->addPluginOption(self::FORM_RADIO_ELEMENT, $distance_unit_field);

        $plugin = new GeoEncoderPlugin();
        if (!$plugin->isConfigured()) {
            $this->addInfoMessage('Please complete plugin setup to start using it.', 'setup');
            $this->addToView('is_configured', false);
        } else {
            $this->addToView('is_configured', true);
        }
        $this->addToView('thinkup_site_url', Utils::getApplicationURL());
        return $this->generateView();
    }
}