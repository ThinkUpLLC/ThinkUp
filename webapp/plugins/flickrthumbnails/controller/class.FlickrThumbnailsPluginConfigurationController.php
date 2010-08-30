<?php
/**
 * FlickrThumbnails Plugin configuration controller
 *
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
