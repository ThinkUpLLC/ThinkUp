<?php
/**
 *
 * ThinkUp/webapp/plugins/flickrthumbnails/controller/class.FlickrThumbnailsPluginConfigurationController.php
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
 */
/**
 * FlickrThumbnails Plugin configuration controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

class FlickrThumbnailsPluginConfigurationController extends PluginConfigurationController {

    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.'plugins/flickrthumbnails/view/flickrthumbnails.account.index.tpl');

        /** set option fields **/
        // API key text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'flickr_api_key',
        'label'=>'Your Flickr API key')); // add element
        $this->addPluginOptionHeader('flickr_api_key',
        'Flickr API key (<a href="http://www.flickr.com/services/api/keys/">Get it here</a>)');
        // set a special required message
        $this->addPluginOptionRequiredMessage('flickr_api_key',
        'The Flickr Thumbnails plugin requires a valid API key.');

        return $this->generateView();
    }
}
