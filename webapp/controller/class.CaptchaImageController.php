<?php
/**
 * CAPTCHA Image Controller
 * Generates a CAPTCHA image with a random number embedded in it.
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
        $this->setContentType('image/png');

        $random_num = rand(1000,99999);
        $_SESSION['ckey'] = md5($random_num);

        $img = rand(1,4);
        $img_handle = imageCreateFromPNG($config->getValue('source_root_path').
        "webapp/assets/img/captcha/bg".$img.".PNG");
        $color = ImageColorAllocate ($img_handle, 0, 0, 0);
        ImageString ($img_handle, 5, 20, 13, $random_num, $color);
        ImagePng ($img_handle);
        ImageDestroy ($img_handle);
    }
}