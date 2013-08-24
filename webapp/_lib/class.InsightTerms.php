<?php
/**
 *
 * ThinkUp/webapp/_lib/class.InsightTerms.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

class InsightTerms {
    /**
     * Singular quantity
     * @var bool
     */
    const SINGULAR = false;
    /**
     * Plural quantity
     * @var bool
     */
    const PLURAL = true;
    /**
     * Network whose localization has to be followed
     * @var str
     */
    var $network;

    public function __construct($network) {
        $this->network = $network;
    }

    /**
     * Get the appropriate noun for a term.
     * @param str $noun the term to be localized
     * @param bool $quantity whether the term to be returned is plural or not
     * @return str localized noun for the required term
     */
    public function getNoun($noun, $quantity = self::SINGULAR) {
        switch ($noun) {
            case 'post':
                return self::getNounForPost($this->network, $quantity);
                break;

            case 'like':
            case 'favorite':
            case 'favlike':
                return self::getNounForFavlike($this->network, $quantity);
                break;

            case 'reply':
                return self::getNounForReply($this->network, $quantity);
                break;

            case 'retweet':
            case 'reshare':
                return self::getNounForReshare($this->network, $quantity);
                break;

            case 'friend':
            case 'follower':
                return self::getNounForFriend($this->network, $quantity);
                break;

            default:
                return null;
                break;
        }
    }

    /**
     * Get the appropriate verb for an action.
     * @param str $verb the action that has to be localized
     * @return str localized verb for the required action
     */
    public function getVerb($verb) {
        switch ($verb) {
            case 'posted':
                return self::getPastTenseVerbToPost($this->network);
                break;

            case 'liked':
            case 'favorited':
            case 'favliked':
                return self::getPastTenseVerbToFavlike($this->network);
                break;

            case 'shared':
                return self::getPastTenseVerbToShare($this->network);
                break;

            default:
                return null;
                break;
        }
    }

    /**
     * Get the localized noun for the term 'post'.
     * @param str $network network whose localization has to be followed
     * @param bool $plural whether the term to be returned is plural or not
     * @return str localized noun for the term 'post'
     */
    private function getNounForPost($network, $plural) {
        switch ($network) {
            case 'twitter':
                return (!$plural) ? 'tweet' : 'tweets';
                break;

            case 'facebook':
                return (!$plural) ? 'status update' : 'status updates';
                break;

            case 'foursquare':
                return (!$plural) ? 'checkin' : 'checkins';
                break;

            default:
                return (!$plural) ? 'post' : 'posts';
                break;
        }
    }

    /**
     * Get the localized noun for the term 'like'.
     * @param str $network network whose localization has to be followed
     * @param bool $plural whether the term to be returned is plural or not
     * @return str localized noun for the term 'like'
     */
    private function getNounForFavlike($network, $plural) {
        switch ($network) {
            case 'twitter':
                return (!$plural) ? 'favorite' : 'favorites';
                break;

            case 'google+':
                return (!$plural) ? '+1' : '+1s';
                break;

            default:
                return (!$plural) ? 'like' : 'likes';
                break;
        }
    }

    /**
     * Get the localized noun for the term 'reply'
     * @param str $network network whose localization has to be followed
     * @param bool $plural whether the term to be returned is plural or not
     * @return str localized noun for the term 'reply'
     */
    private function getNounForReply($network, $plural) {
        switch ($network) {
            case 'twitter':
                return (!$plural) ? 'reply' : 'replies';
                break;

            default:
                return (!$plural) ? 'comment' : 'comments';
                break;
        }
    }

    /**
     * Get the localized noun for the term 'reshare'
     * @param str $network network whose localization has to be followed
     * @param bool $plural whether the term to be returned is plural or not
     * @return str localized noun for the term 'reshare'
     */
    private function getNounForReshare($network, $plural) {
        switch ($network) {
            case 'twitter':
                return (!$plural) ? 'retweet' : 'retweets';
                break;

            default:
                return (!$plural) ? 'reshare' : 'reshares';
                break;
        }
    }

    /**
     * Get the localized noun for the term 'friend'
     * @param str $network network whose localization has to be followed
     * @param bool $plural whether the term to be returned is plural or not
     * @return str localized noun for the term 'friend'
     */
    private function getNounForFriend($network, $plural) {
        switch ($network) {
            case 'twitter':
                return (!$plural) ? 'follower' : 'followers';
                break;

            default:
                return (!$plural) ? 'friend' : 'friends';
                break;
        }
    }

    /**
     * Get the localized verb for the action 'posted'
     * @param str $network network whose localization has to be followed
     * @return str localized verb for the action 'posted'
     */
    private function getPastTenseVerbToPost($network) {
        switch ($network) {
            case 'twitter':
                return 'tweeted';
                break;

            default:
                return 'posted';
                break;
        }
    }

    /**
     * Get the localized verb for the action 'liked'
     * @param str $network network whose localization has to be followed
     * @return str localized verb for the action 'liked'
     */
    private function getPastTenseVerbToFavlike($network) {
        switch ($network) {
            case 'twitter':
                return 'favorited';
                break;

            case 'google+':
                return '+1\'d';
                break;

            default:
                return 'liked';
                break;
        }
    }

    /**
     * Get the localized verb for the action 'shared'
     * @param str $network network whose localization has to be followed
     * @return str localized verb for the action 'shared'
     */
    private function getPastTenseVerbToShare($network) {
        switch ($network) {
            case 'twitter':
                return 'retweeted';
                break;

            default:
                return 'reshared';
                break;
        }
    }
}