<?php
/**
 * Plugin
 *
 * A ThinkUp plugin
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class Plugin {
    /*
     * @var int id
     */
    var $id;
    /*
     * @var str plugin name
     */
    var $name;
    /*
     * @var str folder name
     */
    var $folder_name;
    /*
     * @var istr description
     */
    var $description;
    /*
     * @var str author
     */
    var $author;
    /*
     * @var str homepage
     */
    var $homepage;
    /*
     * @var float version
     */
    var $version;
    /*
     * @var bool is active
     */
    var $is_active = false;
    /*
     * @var str plugin icon
     */
    var $icon;

    public function __construct($val = null) {
        if(! $val) {
            return;
        }
        if (isset($val["id"])) {
            $this->id = $val["id"];
        }
        $this->name = $val["name"];
        $this->folder_name = $val["folder_name"];
        $this->description = $val['description'];
        $this->author = $val['author'];
        $this->homepage = $val['homepage'];
        $this->version = $val['version'];
        if (isset($val['icon'])) {
            $this->icon = $val['icon'];
        }
        if ($val['is_active'] == 1) {
            $this->is_active = true;
        } else {
            $this->is_active = false;
        }
    }

}
