<?php
/**
 * Webapp Tab Dataset
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class WebappTabDataset {
    /**
     * @var str
     */
    var $name;
    /**
     * @var str
     */
    var $dao_name;
    /**
     *
     * @var str
     */
    var $dao_method_name;
    /**
     *
     * @var array
     */
    var $method_params;

    /**
     *
     * @var array String of allowed DAO names
     */
    var $FETCHING_DAOS = array('FollowDAO', 'PostDAO', 'LinkDAO', 'FollowerCountDAO');

    /**
     * Constructor
     * @param str $name
     * @param str $dao_name
     * @param str $dao_method_name
     * @param array $method_params
     * @return WebappTabDataset
     */
    public function __construct($name, $dao_name, $dao_method_name, $method_params=array()) {
        $this->name = $name;
        if (in_array($dao_name, $this->FETCHING_DAOS)) {
            $this->dao_name = $dao_name;
            $this->dao_method_name = $dao_method_name;
            $this->method_params = $method_params;
        } else {
            throw new Exception($dao_name . ' is not one of the allowed DAOs');
        }
    }

    /**
     * Retrieve dataset
     * Run the specified DAO method and return results
     * @return array DAO method results
     */
    public function retrieveDataset() {
        $dao = DAOFactory::getDAO($this->dao_name);
        if (method_exists($dao, $this->dao_method_name)) {
            return call_user_func_array(array($dao, $this->dao_method_name), $this->method_params);
        } else {
            throw new Exception($this->dao_name . ' does not have a ' . $this->dao_method_name . ' method.');
        }
    }
}