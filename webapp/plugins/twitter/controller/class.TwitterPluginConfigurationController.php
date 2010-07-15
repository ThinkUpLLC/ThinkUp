<?php
/**
 * Twitter Plugin Configuration Controller
 *
 * Handles plugin configuration requests.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TwitterPluginConfigurationController extends ThinkTankAuthController {
    /**
     *
     * @var Owner
     */
    var $owner;
    /**
     * Constructor
     * @param Owner $owner
     */
    public function __construct($owner) {
        parent::__construct(true);
        $this->owner = $owner;
        $this->disableCaching();
    }

    public function authControl() {
        $config = Config::getInstance();
        $this->setViewTemplate($config->getValue('source_root_path').
        'webapp/plugins/twitter/view/twitter.account.index.tpl');

        $id = DAOFactory::getDAO('InstanceDAO');
        $od = DAOFactory::getDAO('OwnerDAO');

        $oauth_consumer_key = $config->getValue('oauth_consumer_key');
        $oauth_consumer_secret = $config->getValue('oauth_consumer_secret');

        //Add public user instance
        if (isset($_GET['twitter_username'])) { // if form was submitted
            $logger = Logger::getInstance();

            //Check user exists and is public
            $api = new TwitterAPIAccessorOAuth('NOAUTH', 'NOAUTH', $oauth_consumer_key, $oauth_consumer_secret,
            $config->getValue('archive_limit'));
            $api_call = str_replace("[id]", $_GET['twitter_username'], $api->cURL_source['show_user']);
            list($cURL_status, $data) = $api->apiRequestFromWebapp($api_call);
            if ($cURL_status == 200) {
                $thisFeed = array();
                try {
                    $xml = $api->createParserFromString(utf8_encode($data));
                    $user = array('user_id'=>$xml->id, 'user_name'=>$xml->screen_name, 'is_protected'=>$xml->protected);
                } catch(Exception $e) {
                    $this->addErrorMessage($e->getMessage());
                }
                if (isset($user) && $user["is_protected"] == 'false') {
                    // if so, add to instances table and owners table

                    $i = $id->getByUsernameOnNetwork($_GET['twitter_username'], 'twitter');
                    $oid = DAOFactory::getDAO('OwnerInstanceDAO');;

                    $msg = '';
                    if (isset($i)) { //Instance exists
                        $oi = $oid->get($this->owner->id, $i->id);
                        if ($oi == null) { //Owner_instance doesn't exist
                            $oid->insert($this->owner->id, $i->id, '', '');
                        }
                    } else { //Instance does not exist
                        $id->insert($user["user_id"], $user["user_name"]);

                        $i = $id->getByUsernameOnNetwork($user["user_name"], 'twitter');
                        $oid->insert($this->owner->id, $i->id, '', '');
                    }
                    $this->addSuccessMessage($_GET['twitter_username']." has been added to ThinkTank.");

                    $this->addSuccessMessage("Added ".$_GET['twitter_username']." to ThinkTank.");
                } else { // if not, return error
                    $this->addErrorMessage($_GET['twitter_username'].
                    " is a private Twitter account; ThinkTank cannot track it without authorization.");
                }
            } else {
                $this->addErrorMessage($_GET['twitter_username']." is not a valid Twitter username.");
            }
        }

        $to = new TwitterOAuth($oauth_consumer_key, $oauth_consumer_secret);
        /* Request tokens from twitter */
        $tok = $to->getRequestToken();
        if (isset($tok['oauth_token'])) {
            $token = $tok['oauth_token'];
            $_SESSION['oauth_request_token_secret'] = $tok['oauth_token_secret'];

            /* Build the authorization URL */
            $oauthorize_link = $to->getAuthorizeURL($token);
        } else {
            //set error message here
            $this->addErrorMessage(
            "Unable to obtain OAuth token. Check your Twitter consumer key and secret configuration.");
            $oauthorize_link = '';
        }

        $owner_instances = $id->getByOwnerAndNetwork($this->owner, 'twitter');

        $this->addToView('owner_instances', $owner_instances);
        $this->addToView('oauthorize_link', $oauthorize_link);

        return $this->generateView();
    }
}
