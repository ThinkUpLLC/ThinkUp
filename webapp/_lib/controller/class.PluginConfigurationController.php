<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.PluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Gina Trapani
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
 * Plugin Configuration Controller
 * Extends ThinkUpAuthController to add plugin configuration option functionality
 *  <code>
 *
 *      $this->addPluginOption(FORM_TEXT_ELEMENT, array('name' => 'email') );
 *      // you can add a header for an option
 *      $this->addPluginOptionHeader('email', 'Please add an email address for this plugin so we can spam you');
 *      // you can also set a special message for required options, the default is:
 *      //  "Please enter a value for the field '{name}'
 *      $this->addPluginOptionRequiredMessage('email', 'You must enter an email so we can spam you');
 *
 *      // you can set a default value for a text element
 *      $this->addPluginOption(FORM_TEXT_ELEMENT, array('name' => 'Location', default => 'New York') );
 *
 *      // by default an option is required, but can be set as optional
 *      $this->setPluginOptionRequired('Bio', false);
 *      $this->addPluginOption(FORM_TEXTAREA_ELEMENT, array('name' => 'Bio') );
 *
 *      // can set a validation regex, in this case service_id must be an integer
 *      $this->addPluginOption(FORM_TEXT_ELEMENT, array('name' => 'service_id', validation_regex => '^\d+$) );
 *
 *      // can set optional label for element
 *      $this->addPluginOption(FORM_TEXT_ELEMENT, array('name' => 'phone', 'label' => "Phone Number") );
 *
 *      $this->addPluginOption(FORM_RADIO_ELEMENT,
 *          array('name' => 'Gender', value => 'F', 'display_value' => 'Female') );
 *      $this->addPluginOption(FORM_RADIO_ELEMENT,
 *          array('name' => 'Gender', value => 'M', 'display_value' => 'Male') );
 *      $this->addPluginOption(FORM_RADIO_ELEMENT,
 *          array('name' => 'Gender', value => 'O', 'display_value' => 'Other', 'default_selection' => true) );
 *
 *      //select element
 *      $this->addPluginOption(FORM_SELECT_ELEMENT,
 *          array('name' => 'City', value => 'NYC', 'display_value' => 'New York', default_selection' => true ) );
 *
 *      $this->addPluginOption(FORM_RADIO_ELEMENT,
 *          array('name' => 'Gender', value => 'MSP', 'display_value' => 'Minneapolis') );
 *      $this->addPluginOption(FORM_RADIO_ELEMENT,
 *          array('name' => 'Gender', value => 'LA') );
 *
 *  </code>
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Gina Trapani
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

abstract class PluginConfigurationController extends ThinkUpAuthController {

    /**
     * @const Options markup smarty template
     */
    const OPTIONS_TEMPLATE = '_plugin.options.tpl';
    /**
     * @const Text Form element
     */
    const FORM_TEXT_ELEMENT = 'text_element';
    /**
     * @const radio element
     */
    const FORM_RADIO_ELEMENT = 'radio_element';
    /**
     * @const checkbox element
     */
    const FORM_SELECT_ELEMENT = 'select_element';
    /**
     * @var Array list of option elements
     */
    var $option_elements = array();
    /**
     * @var Array list of option element headers
     */
    var $option_headers = array();

    /**
     * @var Array list of not required options
     */
    var $option_not_required = array();

    /**
     * @var Array list of required failed messages
     */
    var $option_required_message = array();

    /**
     * @var Array list select multi
     */
    var $option_select_multiple = array();

    /**
     * @var Array list select visible
     */
    var $option_select_visible = array();

    /**
     * @var Owner
     */
    var $owner;

    /**
     * @var str folder name
     */
    var $folder_name;

    /**
     * @var int plugin id
     */
    var $plugin_id;

    /**
     * @var array plugin values
     */
    var $options_values = array();

    /**
     * @var array plugin values
     */
    var $options_hash = array();

    /**
     * Whether or not to show button to add user
     * @var bool
     */
    var $do_show_add_button = true;

    public function __construct($owner, $folder_name) {
        parent::__construct(true);
        $this->owner = $owner;
        $this->folder_name = $folder_name;
        $this->disableCaching();
        //get option values
        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        $this->options_values  = $plugin_option_dao->getOptions($this->folder_name);
        if (isset($this->options_values[0])) {
            $this->plugin_id = $this->options_values[0]->plugin_id;
        } else {
            $plugin_dao = DAOFactory::getDAO('PluginDAO');
            $this->plugin_id = $plugin_dao->getPluginId($folder_name);
        }
        if (($owner instanceof Owner) && $owner != null && $owner->isProLevel()) {
            // For Pro users, cap instances at 10
            $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
            $owner_instances = $owner_instance_dao->getByOwner($this->owner->id);
            $total_instances = sizeof($owner_instances);
            if ($total_instances >= 9) {
                $this->do_show_add_button = false;
            }
            $this->addInfoMessage("As a Pro member, you've connected ".$total_instances." of 10 accounts to ThinkUp.",
            'membership_cap');
        }
    }

    /**
     * Generates plugin page options markup - Calls parent::generateView()
     *
     * @return str view markup
     */
    protected function generateView() {
        // if we have some p[lugin option elements defined
        // render them and add to the parent view...
        if (count($this->option_elements) > 0) {
            $this->setValues();
            $view_mgr = new ViewManager();
            $view_mgr->disableCaching();
            // assign data
            $view_mgr->assign('option_elements', $this->option_elements);
            $view_mgr->assign('option_elements_json', json_encode($this->option_elements));
            $view_mgr->assign('option_headers', $this->option_headers);
            $view_mgr->assign('option_not_required', $this->option_not_required);
            $view_mgr->assign('option_not_required_json', json_encode($this->option_not_required));
            $view_mgr->assign('option_required_message', $this->option_required_message);
            $view_mgr->assign('option_required_message_json', json_encode($this->option_required_message));
            $view_mgr->assign('option_select_multiple', $this->option_select_multiple);
            $view_mgr->assign('option_select_visible', $this->option_select_visible);
            $view_mgr->assign('plugin_id', $this->plugin_id);
            $view_mgr->assign('user_is_admin', $this->isAdmin());
            $options_markup = '';
            if ($this->profiler_enabled) {
                $view_start_time = microtime(true);
                $options_markup = $view_mgr->fetch(self::OPTIONS_TEMPLATE);
                $view_end_time = microtime(true);
                $total_time = $view_end_time - $view_start_time;
                $profiler = Profiler::getInstance();
                $profiler->add($total_time, "Rendered view (not cached)", false);
            } else  {
                $options_markup = $view_mgr->fetch(self::OPTIONS_TEMPLATE);
            }
            $this->addToView('options_markup', $options_markup);
        }
        return parent::generateView();
    }

    /**
     * Add a header for an option field
     * @param  str Option name
     * @param  str OptionHeader
     */
    public function addPluginOptionHeader($name, $message) {
        $this->option_headers[$name] = $message;
    }

    /**
     * set an option as not required
     * @param  str option name
     */
    public function setPluginOptionNotRequired($name) {
        $this->option_not_required[$name] = true;
    }


    /**
     * Add a required message for an option field
     * @param  str message
     */
    public function addPluginOptionRequiredMessage($name, $message) {
        $this->option_required_message[$name] = $message;
    }

    /**
     * @param  str Constant value FORM_*_ELEMENT
     * @param  array Arguments for a particular element
     */
    public function addPluginOption($option_type, $args) {

        if (isset($args['name'])) {

            $element = array('name' => $args['name'], 'type' => $option_type);
            switch($option_type) {
                case self::FORM_SELECT_ELEMENT:
                    $element['values'] = $args['values'];
                    break;
                case self::FORM_RADIO_ELEMENT:
                    $element['values'] = $args['values'];
                    break;
                default:
                    // text field, do nothing...
                    if (isset($args['validation_regex'])) {
                        $element['validation_regex'] = $args['validation_regex'];
                    }

            }
            if (isset($args['default_value'])) {
                $element['default_value'] = $args['default_value'];
            }
            if (isset($args['label'])) {
                $element['label'] = $args['label'];
            }
            if (isset($args['id'])) {
                $element['id'] = $args['id'];
            }
            if (isset($args['value'])) {
                $element['value'] = $args['value'];
            }
            if (isset($args['size'])) {
                $element['size'] = $args['size'];
            }
            if (isset($args['advanced'])) {
                $element['advanced'] = true;
                // advanced options should not be required
                $this->setPluginOptionNotRequired($args['name']);
            }
            $this->option_elements[$args['name']] = $element;

        }
    }

    /**
     * Sets the values for options in the data store for the view
     */
    public function setValues() {
        $options_hash = $this->optionList2HashByOptionName();
        foreach( $this->option_elements as $key => $value) {
            if (isset($options_hash[$key])) {
                $this->option_elements[$key]['id'] = $options_hash[$key]->id;
                $this->option_elements[$key]['value'] = $options_hash[$key]->option_value;
            } else {
                if (isset($this->option_elements[$key]['default_value'])) {
                    $this->option_elements[$key]['value'] = $this->option_elements[$key]['default_value'];
                }
            }
        }
    }

    /**
     * Gets Hash of Option Name/Values
     * @return array A hash of plugin options with option_name as the key
     */
    public function getPluginOptions() {
        return $this->optionList2HashByOptionName();
    }

    /**
     * Gets a plugin option value by key/name
     * @return str a plugin value for passed key
     */
    public function getPluginOption($key) {
        $options_hash = $this->optionList2HashByOptionName();
        $value = isset( $options_hash[$key] ) ? $options_hash[$key]->option_value : null;
        return $value;
    }

    /**
     * Converts a list of plugin options to a hash with option_name as the key
     * @param array A list of Plugin Options
     * @return array A hash table op Options with option_name as the key
     */
    public function optionList2HashByOptionName() {
        if (count($this->options_values) > 0 && count($this->options_hash) == 0) {
            foreach ($this->options_values as $option) {
                $this->options_hash[ $option->option_name ] = $option;
            }
        }
        return $this->options_hash;
    }
}