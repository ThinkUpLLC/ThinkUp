<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.CaptchaImageController.php
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
 * CAPTCHA Image Controller
 * Generates a CAPTCHA image with a random number embedded in it.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class CaptchaImageController extends ThinkUpController {
    public function control() {
    }

    /**
     * Override the parent's go method because there is no view manager here--we're outputting the image directly.
     */
    public function go() {
        $config = Config::getInstance();

        $random_num = rand(1000,99999);
        SessionCache::put('ckey', md5($random_num));

        $img = rand(1,4);
        Loader::definePathConstants();
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
}