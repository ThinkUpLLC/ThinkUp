Captcha
=======

ThinkUp/webapp/_lib/model/class.Captcha.php

Copyright (c) 2009-2011 Gina Trapani

CAPTCHA generator
Registration "Prove you're human" CAPTCHA image, with reCAPTCHA support.


Properties
----------

type
~~~~



msg
~~~



pubkey
~~~~~~



prikey
~~~~~~



site_root
~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
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


generate
~~~~~~~~



.. code-block:: php5

    <?php
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


check
~~~~~



.. code-block:: php5

    <?php
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
                    if (strcmp(md5($_POST['user_code']), SessionCache::get('ckey'))) {
                        $this->msg = "Wrong text, try again";
                        return false;
                    } else {
                        return true;
                    }
                    break;
            }
        }




