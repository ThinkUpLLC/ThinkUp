<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Captcha.php
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
 *
 *
 * CAPTCHA generator
 * Registration "Prove you're human" CAPTCHA image, with reCAPTCHA support.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Captcha {
    var $type;
    var $msg = false;
    private $pubkey;
    private $prikey;
    private $site_root;

    public function __construct() {
        $config = Config::getInstance();
        $this->site_root = $config->getValue('site_root_path');

        if ($config->getValue('recaptcha_enable')) {
            $this->type = 1;
            Utils::defineConstants();
            require_once THINKUP_WEBAPP_PATH.'_lib/extlib/recaptcha-php-1.10/recaptchalib.php';
            $this->pubkey = $config->getValue('recaptcha_public_key');
            $this->prikey = $config->getValue('recaptcha_private_key');
        } else {
            $this->type = 0;
        }
    }
    public function generate() {
        switch ($this->type) {
            case 1:
                $code = recaptcha_get_html($this->pubkey, $this->msg);
                return $code;
                break;
            default:
                if (isset($this->msg)) {
                    return "<input name=\"user_code\" type=\"text\" size=\"10\"><img src=\"".$this->site_root.
                    "session/captcha-img.php\" align=\"middle\"><span style=\"color: #FF0000\">".$this->msg."</span>";
                } else {
                    return "<input name=\"user_code\" type=\"text\" size=\"10\"><img src=\"".$this->site_root.
                    "session/captcha-img.php\" align=\"middle\">&nbsp;";
                }
                break;
        }
    }
    public function check() {
        switch ($this->type) {
            case 1:
                $resp = recaptcha_check_answer($this->prikey, $_SERVER["REMOTE_ADDR"],
                $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
                if (!$resp->is_valid) {
                    $this->msg = $resp->error;
                    return false;
                } else {
                    return true;
                }
                break;
            default:
                if (strcmp(md5($_POST['user_code']), $_SESSION['ckey'])) {
                    $this->msg = "Wrong text, try again";
                    return false;
                } else {
                    return true;
                }
                break;
        }
    }
}
