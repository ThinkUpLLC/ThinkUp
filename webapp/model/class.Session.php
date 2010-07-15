<?php
/**
 * Session
 *
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 *
 */
class Session {
    /**
     *
     * @var mixed
     */
    private $data;
    /**
     *
     * @var str
     */
    private $salt = "ab194d42da0dff4a5c01ad33cb4f650a7069178b";

    /**
     * Constructor
     * @return Session
     */
    public function __construct() {
        if (isset($_SESSION)) {
            $data = $_SESSION;
        }
    }

    /**
     * @return bool Is user logged into ThinkTank
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['user'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return bool Is user logged into ThinkTank an admin
     */
    public function isAdmin() {
        if (isset($_SESSION['user_is_admin'])) {
            return $_SESSION['user_is_admin'];
        } else {
            return false;
        }
    }

    /**
     *
     * @param str $pwd Password
     * @return str MD5-hashed password
     */
    private function md5pwd($pwd) {
        return md5($pwd);
    }

    /**
     *
     * @param str $pwd Password
     * @return str SHA1-hashed password
     */
    private function sha1pwd($pwd) {
        return sha1($pwd);
    }
    /**
     *
     * @param str $pwd
     * @return str Salted SHA1 password
     */
    private function saltedsha1($pwd) {
        return sha1(sha1($pwd.$this->salt).$this->salt);
    }

    /**
     * Encrypt password
     * @param str $pwd password
     * @return str Encrypted password
     */
    public function pwdCrypt($pwd) {
        return $this->saltedsha1($pwd);
    }

    /**
     * Check password
     * @param str $pwd Password
     * @param str $result Result
     * @return bool Whether or submitted password matches check
     */
    public function pwdCheck($pwd, $result) {
        if ($this->saltedsha1($pwd) == $result || $this->sha1pwd($pwd) == $result || $this->md5pwd($pwd) == $result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Complete login action
     * @param Owner $owner
     */
    public function completeLogin($owner) {
        $_SESSION['user'] = $owner->user_email;
        $_SESSION['user_is_admin'] = $owner->is_admin;
    }

    /**
     * Log out
     */
    public function logout() {
        unset($_SESSION['user']);
        unset($_SESSION['user_is_admin']);
    }
}
