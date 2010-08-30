<?php
/**
 * CrawlerAuth Controller
 *
 * Runs crawler from the command line given valid command line credentials.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class CrawlerAuthController extends ThinkUpController {

    /**
     *
     * @var int The number of arguments passed to the crawler
     */
    var $argc;

    /**
     *
     * @var array The array of arguments passed to the crawler
     */
    var $argv;
    /**
     * Constructor
     *
     * @param boolean $session_started
     */
    public function __construct($argc, $argv) {
        parent::__construct(true);
        $this->argc = $argc;
        $this->argv = $argv;
    }

    public function control() {
        $output = "";
        $authorized = false;

        if (isset($this->argc) && $this->argc > 1) { // check for CLI credentials
            $session = new Session();
            $username = $this->argv[1];
            if ($this->argc > 2) {
                $pw = $this->argv[2];
            } else {
                $pw = getenv('THINKUP_PASSWORD');
            }

            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $passcheck = $owner_dao->getPass($username);
            if ($session->pwdCheck($pw, $passcheck)) {
                $authorized = true;
                $_SESSION['user'] = $username;
            } else {
                $output = "ERROR: Incorrect username and password.";
            }
        } else { // check user is logged in on the web
            if ( $this->isLoggedIn() ) {
                $authorized = true;
            } else {
                $output = "ERROR: Invalid or missing username and password.";
            }
        }

        if ($authorized) {
            $crawler = Crawler::getInstance();
            $crawler->crawl();
        }

        return $output;
    }
}