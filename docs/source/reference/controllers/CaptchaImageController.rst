CaptchaImageController
======================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.CaptchaImageController.php

Copyright (c) 2009-2011 Gina Trapani

CAPTCHA Image Controller
Generates a CAPTCHA image with a random number embedded in it.



Methods
-------

control
~~~~~~~



.. code-block:: php5

    <?php
        public function control() {
        }


go
~~

Override the parent's go method because there is no view manager here--we're outputting the image directly.

.. code-block:: php5

    <?php
        public function go() {
            $config = Config::getInstance();
    
            $random_num = rand(1000,99999);
            SessionCache::put('ckey', md5($random_num));
    
            $img = rand(1,4);
            Utils::defineConstants();
            $captcha_bg_image_path = THINKUP_WEBAPP_PATH."assets/img/captcha/bg".$img.".PNG";
            $img_handle = imageCreateFromPNG($captcha_bg_image_path);
            if ($img_handle===false) {
                echo 'CAPTCHA image could not be created from '.$captcha_bg_image_path;
            } else {
                $this->setContentType('image/png');
                $this->sendHeader();
                $color = ImageColorAllocate ($img_handle, 0, 0, 0);
                ImageString ($img_handle, 5, 20, 13, $random_num, $color);
                ImagePng ($img_handle);
                ImageDestroy ($img_handle);
            }
        }




