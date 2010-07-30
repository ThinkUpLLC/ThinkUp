<?php
/**
 * ThinkUp User, i.e., owner of social network user accounts
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Owner {
    /**
     * @var int
     */
    var $id;
    /**
     *
     * @var str
     */
    var $full_name;
    /**
     *
     * @var str
     */
    var $email;
    /**
     *
     * @var bool Default false
     */
    var $is_admin = false;
    /**
     *
     * @var bool Default false
     */
    var $is_activated = false;
    /**
     *
     * @var str Date
     */
    var $last_login;
    /**
     *
     * @var array Of instances
     */
    var $instances = null;
    /**
     * Token to email to user for resetting password
     * @var str
     */
    var $password_token;

    /**
     * Constructor
     * @param array $val Key/value pairs to construct Owner
     * @return Owner
     */
    public function __construct($val=false) {
        if ($val) {
            $this->id = $val["id"];
            $this->full_name = $val["full_name"];
            $this->email = $val['email'];
            $this->last_login = $val['last_login'];
            $this->is_admin = PDODAO::convertDBToBool($val["is_admin"]);
            $this->is_activated = PDODAO::convertDBToBool($val["is_activated"]);
        }
    }

    /**
     * Setter
     * @param array $instances
     */
    public function setInstances($instances) {
        $this->instances = $instances;
    }

    /**
     * Generates a new password recovery token and returns it.
     *
     * The internal format of the token is a Unix timestamp of when it was
     * set (for checking if it's stale), an underscore, and then the token
     * itself.
     *
     * @return string A new password token for embedding in a link and emailing a user.
     */
    public function setPasswordRecoveryToken() {
        $token = md5(uniqid(rand()));
        $this->password_token = time() . '_' . $token;
        /** @TODO: save this record */
        return $token;
    }
}

