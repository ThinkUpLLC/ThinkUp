<?php
/**
 * HelloThinkUp Plugin configuration controller
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */

class HelloThinkUpPluginConfigurationController extends PluginConfigurationController {


    public function authControl() {
        $config = Config::getInstance();
        $this->setViewTemplate( $config->getValue('source_root_path')
        . 'webapp/plugins/hellothinkup/view/hellothinkup.account.index.tpl');
        $this->addToView('message',
            'Hello, world! This is the example plugin configuration page for  '.$this->owner->email .'.');

        /** set option fields **/
        // name text field
        $name_field = array('name' => 'testname', 'label' => 'Enter Your Name'); // set an element name and label
        $name_field['default_value'] = 'Think Tank User'; // ste default value
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field); // add elemrnt
        // set testname header
        $this->addPluginOptionHeader('testname', 'User Info'); // add a header for an element
        // set a special required message
        $this->addPluginOptionRequiredMessage('testname',
            'Please enter a name, because we\'d really like to have one...');

        // gender radio field
        $gender_field = array('name' => 'testgender', 'label' => 'Select a Gender'); // set an element name and label
        $gender_field['values'] = array('Female' => 1, 'Male' => 2, 'Other' => 3);
        $gender_field['default_value'] = '3'; // set default value
        $this->addPluginOption(self::FORM_RADIO_ELEMENT, $gender_field); // add element

        // Birth Year Select
        $bday_field = array('name' => 'testbirthyear', 'label' => 'Select The Year You Were Born');
        $years = array();
        $i = 1900;
        while ($i <= 2010) {
            $years['Born in ' . $i] = $i;
            $i++;
        }
        $bday_field['values'] =  $years;
        $bday_field['default_value'] = '2005';
        $this->addPluginOption(self::FORM_SELECT_ELEMENT, $bday_field);

        // Enable registration stuff
        $reg_field = array('name' => 'testregopen', 'label' => 'Open Registration');
        $this->addPluginOptionHeader('testregopen', 'Registration Options');
        $reg_field['values'] = array('Open' => 1, 'Closed' => 0);
        $this->addPluginOption(self::FORM_RADIO_ELEMENT, $reg_field);

        // registration key
        $reg_key = array('name' => 'RegKey');
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $reg_key);
        $this->setPluginOptionNotRequired('RegKey');
        return $this->generateView();

    }

}

