<?php
/**
 * GeoEncoder Plugin configuration controller
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */

class GeoEncoderPluginConfigurationController extends PluginConfigurationController {


    public function authControl() {
        $config = Config::getInstance();
        $this->setViewTemplate( $config->getValue('source_root_path')
        . 'webapp/plugins/geoencoder/view/geoencoder.account.index.tpl');
        $this->addToView('message',
            'This is the GeoEncoder plugin configuration page for '.$this->owner->email .'.');

        /** set option fields **/
        // gmaps_api_key text field
        $name_field = array('name' => 'gmaps_api_key', 'label' => 'Enter Your Google Maps API Key');
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field);
        $this->addPluginOptionHeader('gmaps_api_key', 'GeoEncoder Plugin Options');
        $this->addPluginOptionRequiredMessage('gmaps_api_key',
            'Please enter your Google Maps API Key');

        // distance_unit radio field
        $distance_unit_field = array('name' => 'distance_unit', 'label' => 'Select Unit of Distance');
        $distance_unit_field['values'] = array('Kilometers' => 'km', 'Miles' => 'mi');
        $distance_unit_field['default_value'] = 'km'; 
        $this->addPluginOption(self::FORM_RADIO_ELEMENT, $distance_unit_field);

        return $this->generateView();
    }
}