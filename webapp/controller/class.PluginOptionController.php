<?php
/**
 * Plugin Option Controller
 *
 * Controller to add and update plugin options
 *
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */

class PluginOptionController extends ThinkUpAdminController {

    public function authControl() {

        // set inital state
        $this->json = array('status' => 'failed');

        // verify we have a proper action and plugin id
        if (isset($_GET['action']) && $_GET['action'] == 'set_options') {
            if(isset($_GET['plugin_id']) 
                && is_numeric( $_GET['plugin_id'] ) 
                && $this->isValidPluginId( $_GET['plugin_id'] ) ) {

                $this->setPluginOptions($_GET['plugin_id']);

            } else {
                // or fail
                $this->json['message'] = 'Bad plugin id defined for this request';
            }

        } else {
            // or fail
            $this->json['message'] = 'No action defined for this request';
        }
        $this->setJsonData($this->json);
        return $this->generateView();
    }


    /*
     * sets plugin options
     */
    public function setPluginOptions($plugin_id) {
        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptions($plugin_id);
        $cnt = 0;
        $inserted = array();
        $deleted = 0;
        foreach($_GET as $key => $value ) {
            if( preg_match('/^option_/', $key) ) {
                $name = preg_replace('/^option_/', '', $key);
                $id_name = "id_option_" . $name;
                if(isset($_GET[$id_name])) {
                    foreach($options as $option) {
                        //error_log($option->option_name . ' '  . $name);
                        if($option->option_name == $name) {
                            if( $option->option_value != $value ) {
                                $id = preg_replace('/^id_option_/', '', $_GET[$id_name]);
                                if($value == '') {
                                    $plugin_option_dao->deleteOption($id);
                                    $deleted++;
                                } else {
                                    $plugin_option_dao->updateOption($id, $name, $value);
                                }
                                $cnt++;
                            }
                        }
                    }
                } else {
                    $insert_id = $plugin_option_dao->insertOption($plugin_id, $name, $value);
                    if(!  $insert_id) {
                        $this->json_data['message'] = "Unable to add plugin option: $name";
                        return;
                    } else {
                        $inserted[$name] = $insert_id;
                        $cnt++;
                    }                        
                }
            }
        }
        $this->json['results'] = array('updated' => $cnt, 'inserted' => $inserted, 'deleted' => $deleted);
        $this->json['status'] = 'success';
    }


    /*
     * Validates a plugin id
     * @param int A plugin id
     * @return bool
     */
    public function isValidPluginId($plugin_id) {
        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        return $plugin_option_dao->isValidPluginId($plugin_id);
    }
    
}