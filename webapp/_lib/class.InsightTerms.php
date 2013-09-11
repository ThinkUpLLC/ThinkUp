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
     * Get a localized phrase for the action of adding a user as a friend
     * @param str $user the subject who has been added as a friend
     * @return str localized phrase for adding a user as a friend
     **/
    public function getPhraseForAddingAsFriend($user) {
        switch ($this->network) {
            case 'twitter':
                return "followed ".$user;
                break;

            case 'google+':
                return "added ".$user." to new circles";
                break;

            default:
                return "added ".$user." as a friend";
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

    /**
     * Get the human-readable, syntactic time difference .
     * @param int $delta Time difference in seconds
     * @return str Syntactic time difference
     */
    public static function getSyntacticTimeDifference($delta) {
        $tokens = array();
        $tokens['second'] = 1;
        $tokens['minute'] = 60 * $tokens['second'];
        $tokens['hour'] = 60 * $tokens['minute'];
        $tokens['day'] = 24 * $tokens['hour'];

        arsort($tokens);

        foreach ($tokens as $unit => $value) {
            if ($delta < $value) {
                continue;
            } else {
                $number_of_units = floor($delta / $value);
                return $number_of_units.' '.$unit.(($number_of_units > 1) ? 's' : '');
            }
        }
    }

    /**
     * Converts a string containing 1 time or 2 times to once / twice
     * @param  int $number The number to be converted
     * @return str A string expressing the number of occurrences in conversational terms
     */
    public function getOccurrencesAdverb($number) {
        if ($number == 1) {
            return 'once';
        } elseif ($number == 2) {
            return 'twice';
        } else {
            return $number ." times";
        }
    }

    /**
     * Converts a numerical multiplier such as 2 to its string equivalent, such as 'double'.
     * @param  int $number The number to be converted
     * @return str A string expressing the multiplier in conversational terms
     */
    public function getMultiplierAdverb($number) {
        if ($number >= 1) {
            if ($number >= 5) {
                return round($number,2).'x';
            } elseif ($number >= 4) {
                return 'quadruple';
            } elseif ($number >= 3) {
                return 'triple';
            } elseif ($number >= 2) {
                return 'double';
            } else {
                return round($number,2).'x';
            }
        } elseif ($number >= 0.5) {
            return 'half';
        } elseif ($number >= 0.3) {
            return 'a third of';
        } elseif ($number >= 0.25) {
            return 'a quarter of';
        } else {
            return round($number,2).'x';
        }
    }
}
