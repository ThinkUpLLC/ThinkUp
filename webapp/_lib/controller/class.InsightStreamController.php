<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.InsightStreamController.php
 *
 * Copyright (c) 2012 Gina Trapani
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
 * Insights stream controller
 *
 * Displays a list of insights for authenticated service users.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class InsightStreamController extends ThinkUpController {
    public function control() {
        $config = Config::getInstance();
        $this->setViewTemplate('insights.tpl');
        $this->addToView('enable_bootstrap', true);

        if ($this->shouldRefreshCache() ) {
            $insight_dao = DAOFactory::getDAO('InsightDAO');
            $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
            if (Session::isLoggedIn()) {
                if ($this->isAdmin()) {
                    ///show all insights for all service users
                    $insights = $insight_dao->getAllInstanceInsights($page_count=20, $page);
                    $this->addToView('insights', $insights);
                } else {
                    //show only service users owner owns
                }
            } else {
                //show just public service users in stream
                $insights = $insight_dao->getPublicInsights($page_count=10, $page);
                $this->addToView('insights', $insights);
            }
            if (isset($insights) && sizeof($insights) > 0) {
                $this->addToView('next_page', $page+1);
                $this->addToView('last_page', $page-1);
            }
        }
        $this->addToView('developer_log', $config->getValue('is_log_verbose'));
        return $this->generateView();
    }
}