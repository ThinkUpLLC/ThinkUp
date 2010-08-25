<?php
/**
 * Post Controller
 *
 * Displays a post and its replies, retweets, and republishable replies in tabs
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class PostController extends ThinkUpAuthController {
    /**
     *
     * @var PostDAO
     */
    var $post_dao;
    /**
     * Constructor
     * @param boolean $session_started
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->post_dao = DAOFactory::getDAO('PostDAO');
        $this->setPageTitle('Post details');
    }

    /**
     * Main control method
     */
    public function authControl() {
        $this->setViewTemplate('post.index.tpl');
        $network = (isset($_GET['n']) )?$_GET['n']:'twitter';
        $_GET['n'] = $network;
        if ($this->shouldRefreshCache()) {
            if ( isset($_GET['t']) && is_numeric($_GET['t']) && $this->post_dao->isPostInDB($_GET['t'], $network) ){
                $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
                $options = $plugin_option_dao->getOptionsHash('geoencoder', true);
                if (isset($options['distance_unit']->option_value)) {
                    $distance_unit = $options['distance_unit']->option_value;
                } else {
                    $distance_unit = 'km';
                }
                $post_id = $_GET['t'];
                $post = $this->post_dao->getPost($post_id, $network);
                $this->addToView('post', $post);
                $this->addToView('unit', $distance_unit);

                // costly query
                //$this->addToView('likely_orphans', $this->post_dao->getLikelyOrphansForParent($post->pub_date,
                //$post->author_user_id,$post->author_username, 15) );
                //$this->addToView('all_tweets', $this->post_dao->getAllPosts($post->author_user_id, 15) );

                $all_replies = $this->post_dao->getRepliesToPost($post_id, $network, 'default',
                               $distance_unit);
                $this->addToView('replies', $all_replies );
                
                $all_replies_by_location = $this->post_dao->getRepliesToPost($post_id, $network,  'location', 
                                           $distance_unit);
                $this->addToView('replies_by_location', $all_replies_by_location );

                $all_replies_count = count($all_replies);
                $this->addToView('reply_count', $all_replies_count );

                $all_retweets = $this->post_dao->getRetweetsOfPost($post_id, $network, 'default', 
                                $distance_unit);
                $this->addToView('retweets', $all_retweets );
                
                $all_retweets_by_location = $this->post_dao->getRetweetsOfPost($post_id, $network, 'location', 
                                $distance_unit);
                $this->addToView('retweets_by_location', $all_retweets_by_location );

                $retweet_reach = $this->post_dao->getPostReachViaRetweets($post_id, $network);
                $this->addToView('retweet_reach', $retweet_reach);

                $public_replies = $this->post_dao->getPublicRepliesToPost($post_id, $network);
                $public_replies_count = count($public_replies);
                $this->addToView('public_reply_count', $public_replies_count );

                $private_replies_count = $all_replies_count - $public_replies_count;
                $this->addToView('private_reply_count', $private_replies_count );

                $this->addToView('export_params', http_build_query(array(
                    'post_id' => $post_id,
                    'type' => 'replies',
                    'n' => $_GET['n'],
                    'u' => $post->author_username
                )));
            } else {
                $this->addErrorMessage('Post not found');
            }
        }
        return $this->generateView();
    }
}
