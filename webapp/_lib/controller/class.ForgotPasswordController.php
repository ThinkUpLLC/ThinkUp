<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ForgotPasswordController.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * Forgot Password Controller
 * Handles requests for ThinkUp user password reset links.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani
 * @author Michael Louis Thaler <michael.louis.thaler[at]gmail[dot]com>
 */

class ForgotPasswordController extends ThinkUpController {

    public function control() {
        if (isset($_POST['Submit']) && $_POST['Submit'] == 'Send Reset') {
            $this->disableCaching();

            $dao = DAOFactory::getDAO('OwnerDAO');
            $user = $dao->getByEmail($_POST['email']);
            if (isset($user)) {
                $token = $user->setPasswordRecoveryToken();

                $es = new ViewManager();
                $es->caching=false;

                $config = Config::getInstance();
                $es->assign('apptitle', $config->getValue('app_title_prefix')."ThinkUp" );
                $es->assign('recovery_url', "session/reset.php?token=$token");
                $es->assign('server', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
                $es->assign('site_root_path', $config->getValue('site_root_path') );
                $message = $es->fetch('_email.forgotpassword.tpl');

                Mailer::mail($_POST['email'], $config->getValue('app_title_prefix') . "ThinkUp Password Recovery",
                $message);

                $this->addSuccessMessage('Password recovery information has been sent to your email address.');
            } else {
                $this->addErrorMessage('Error: account does not exist.');
            }
        }
        $this->view_mgr->addHelp('forgot', 'userguide/accounts/index');
        $this->setViewTemplate('session.forgot.tpl');
        return $this->generateView();
    }
}
