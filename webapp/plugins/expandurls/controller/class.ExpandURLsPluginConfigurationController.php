<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.BackupController.php
 *
 * Copyright (c) 2009-2010 Mark Wilkie
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Sam Rose
 *
 */
class ExpandURLsPluginConfigurationController extends PluginConfigurationController {
    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.
                'plugins/expandurls/view/expandurls.account.index.tpl');

        $links_to_expand = array(
            'name' => 'links_to_expand',
            'label' => 'Links to expand per crawl',
            'default_value' => 1500
        );
        
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $links_to_expand);

        return $this->generateView();
    }
}
?>
