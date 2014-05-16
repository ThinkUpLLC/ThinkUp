<?php
/**
 *
 * ThinkUp/webapp/_lib/class.Captcha.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * CAPTCHA generator
 * Registration "Prove you're human" CAPTCHA image, with reCAPTCHA support.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Captcha {
    /**
     * ReCAPTCHA type
     * @var int
     */
    const RECAPTCHA_CAPTCHA = 1;
    /**
     * ThinkUp-powered CAPTCHA
     * @var int
     */
    const THINKUP_CAPTCHA = 2;
    /**
     * Type of CAPTCHA being used; should be equal to either self::RECAPTCHA_CAPTCHA or THINKUP_CAPTCHA.
     * @var int
     */
    var $type;

    public function __construct() {
        $config = Config::getInstance();

        if ($config->getValue('recaptcha_enable')) {
            $this->type = self::RECAPTCHA_CAPTCHA;
            Loader::definePathConstants();
            require_once THINKUP_WEBAPP_PATH.'_lib/extlib/recaptcha-php-1.10/recaptchalib.php';
        } else {
            $this->type = self::THINKUP_CAPTCHA;
        }
    }

    /**
     * Generate CAPTCHA HTML code
     * @return str CAPTCHA HTML
     */
    public function generate() {
        switch ($this->type) {
            case self::RECAPTCHA_CAPTCHA:
                $config = Config::getInstance();
                $pub_key = $config->getValue('recaptcha_public_key');
                $priv_key = $config->getValue('recaptcha_private_key');
                $code = recaptcha_get_html($pub_key);
                return $code;
                break;
            default:
                $config = Config::getInstance();
                return
                "<label class=\"control-label\" for=\"user_code\">".
                "<img src=\"".$config->getValue('site_root_path'). "session/captcha-img.php\" class=\"img-responsive\" style=\"\">".
                "</label>".
                "<input name=\"user_code\" id=\"user_code\" type=\"text\" class=\"form-control\" required ".
                "placeholder=\"Please enter the code.\">";
                break;
        }
    }

    /**
     * Check the $_POST'ed CAPTCHA inputs match the contents of the CAPTCHA.
     * @return bool
     */
    public function doesTextMatchImage() {
        //if in test mode, assume check is good if user_code is set to 123456
        if (Utils::isTest()) {
            if (isset($_POST['user_code']) && $_POST['user_code'] == '123456') {
                return true;
            } else {
                return false;
            }
        }

        switch ($this->type) {
            case self::RECAPTCHA_CAPTCHA:
                $config = Config::getInstance();
                $priv_key = $config->getValue('recaptcha_private_key');
                $resp = recaptcha_check_answer($priv_key, $_SERVER["REMOTE_ADDR"],
                $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
                if (!$resp->is_valid) {
                    return false;
                } else {
                    return true;
                }
                break;
            default:
                if (strcmp(md5($_POST['user_code']), SessionCache::get('ckey'))) {
                    return false;
                } else {
                    return true;
                }
                break;
        }
    }
}
