<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.PostController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * Post Controller
 *
 * Displays a post and its replies, retweets, reach, and location information.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class PostController extends ThinkUpController {
    public function control() {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->setPageTitle('Post Replies and Forwards');
        $this->setViewTemplate('post.index.tpl');
        $network = (isset($_GET['n']) )?$_GET['n']:'twitter';
        if ($this->shouldRefreshCache()) {
            if ( isset($_GET['t']) && is_numeric($_GET['t']) ) {
                $post_id = $_GET['t'];
                $post = $post_dao->getPost($post_id, $network);
                if ( isset($post) ){
                    if ( !$post->is_protected || $this->isLoggedIn()) {
                        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
                        $options = $plugin_option_dao->getOptionsHash('geoencoder', true);
                        if (isset($options['distance_unit']->option_value)) {
                            $distance_unit = $options['distance_unit']->option_value;
                        } else {
                            $distance_unit = 'km';
                        }
                        $this->addToView('post', $post);
                        $this->addToView('unit', $distance_unit);

                        $replies = $post_dao->getRepliesToPost($post_id, $network, 'default', $distance_unit);

                        $public_replies = array();
                        foreach ($replies as $reply) {
                            if (!$reply->author->is_protected) {
                                $public_replies[] = $reply;
                            }
                        }
                        $public_replies_count = count($public_replies);
                        $this->addToView('public_reply_count', $public_replies_count );

                        if ($this->isLoggedIn()) {
                            $this->addToView('replies', $replies );
                        } else {
                            $this->addToView('replies', $public_replies );
                        }

                        $retweets = $post_dao->getRetweetsOfPost($post_id, $network, 'default', $distance_unit);
                        $rt_reach = 0;
                        foreach ($retweets as $t) {
                            $rt_reach += $t->author->follower_count;
                            if ($t->is_geo_encoded && $t->reply_retweet_distance > -1 ) {
                                $can_sort_by_proximity = true;
                            }
                        }
                        $this->addToView('retweet_reach', $rt_reach);

                        if ($this->isLoggedIn()) {
                            $this->addToView('retweets', $retweets );
                        } else {
                            $public_rts = array();
                            foreach ($retweets as $rt) {
                                if (!$rt->author->is_protected) {
                                    $public_rts[] = $rt;
                                }
                            }
                            $this->addToView('retweets', $public_rts );
                        }

                        $all_replies_count = count($replies);
                        $private_reply_count = $all_replies_count - $public_replies_count;
                        $this->addToView('private_reply_count', $private_reply_count );
                    } else {
                        $this->addErrorMessage('Insufficient privileges');
                    }
                } else {
                    $this->addErrorMessage('Post not found');
                }
            } else {
                $this->addErrorMessage('Post not specified');
            }
        }
        return $this->generateView();
    }
}