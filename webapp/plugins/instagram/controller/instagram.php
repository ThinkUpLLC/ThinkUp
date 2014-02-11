<?php
/*
 Plugin Name: Instagram
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/instagram/
 Description: Capture and display Instagram posts.
 Class: InstagramPlugin
 Icon: assets/img/instagram_icon.png
 Version: 0.01
 Author: Dimosthenis Nikoudis
 */

$version = explode('.', PHP_VERSION); //dont load instagram plugin for PHP less than 5.3
if ($version[0] >= 5 && $version[1] >= 3) {
    if (!class_exists('Instagram\Instagram')) {
        Loader::addSpecialClass('SplClassLoader', 'plugins/instagram/extlib/SplClassLoader.php');
        $loader = new SplClassLoader('Instagram', 'plugins/instagram/extlib');
        $loader->register();
    }

    $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
    $webapp_plugin_registrar->registerPlugin('instagram', 'InstagramPlugin');

    $crawler_plugin_registrar = PluginRegistrarCrawler::getInstance();
    $crawler_plugin_registrar->registerCrawlerPlugin('InstagramPlugin');
}
