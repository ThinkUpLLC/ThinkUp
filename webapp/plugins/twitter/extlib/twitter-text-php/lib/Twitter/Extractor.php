<?php
/**
 * @author     Mike Cochrane <mikec@mikenz.geek.nz>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 * @package    Twitter
 */

require_once 'Regex.php';

/**
 * Twitter Extractor Class
 *
 * Parses tweets and extracts URLs, usernames, username/list pairs and
 * hashtags.
 *
 * Originally written by {@link http://github.com/mikenz Mike Cochrane}, this
 * is based on code by {@link http://github.com/mzsanford Matt Sanford} and
 * heavily modified by {@link http://github.com/ngnpope Nick Pope}.
 *
 * @author     Mike Cochrane <mikec@mikenz.geek.nz>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 * @package    Twitter
 */
class Twitter_Extractor extends Twitter_Regex {

  /**
   * Provides fluent method chaining.
   *
   * @param  string  $tweet        The tweet to be converted.
   *
   * @see  __construct()
   *
   * @return  Twitter_Extractor
   */
  public static function create($tweet) {
    return new self($tweet);
  }

  /**
   * Reads in a tweet to be parsed and extracts elements from it.
   *
   * Extracts various parts of a tweet including URLs, usernames, hashtags...
   *
   * @param  string  $tweet  The tweet to extract.
   */
  public function __construct($tweet) {
    parent::__construct($tweet);
  }

  /**
   * Extracts all parts of a tweet and returns an associative array containing
   * the extracted elements.
   *
   * @return  array  The elements in the tweet.
   */
  public function extract() {
    return array(
      'hashtags' => $this->extractHashtags(),
      'urls'     => $this->extractURLs(),
      'mentions' => $this->extractMentionedUsernames(),
      'replyto'  => $this->extractRepliedUsernames(),
      'hashtags_with_indices' => $this->extractHashtagsWithIndices(),
      'urls_with_indices'     => $this->extractURLsWithIndices(),
      'mentions_with_indices' => $this->extractMentionedUsernamesWithIndices(),
    );
  }

  /**
   * Extracts all the hashtags from the tweet.
   *
   * @return  array  The hashtag elements in the tweet.
   */
  public function extractHashtags() {
    preg_match_all(self::$patterns['auto_link_hashtags'], $this->tweet, $matches);
    return $matches[3];
  }

  /**
   * Extracts all the URLs from the tweet.
   *
   * @return  array  The URL elements in the tweet.
   */
  public function extractURLs() {
    preg_match_all(self::$patterns['valid_url'], $this->tweet, $matches);
    list($all, $before, $url, $protocol, $domain, $port, $path, $query) = array_pad($matches, 8, '');
    # FIXME: Handle extraction of protocol-less domains and t.co short URLs.
    # https://github.com/twitter/twitter-text-rb/commit/adb6e693b6d003819d615d19219c22d07f114a63
    # https://github.com/twitter/twitter-text-rb/commit/05de2c11a729f93d7680a6d4c12bff6d5ba4c164
    return $url;
  }

  /**
   * Extract all the usernames from the tweet.
   *
   * A mention is an occurrence of a username anywhere in a tweet.
   *
   * @return  array  The usernames elements in the tweet.
   */
  public function extractMentionedUsernames() {
    preg_match_all(self::$patterns['extract_mentions'], $this->tweet, $matches);
    list($all, $before, $username, $after) = array_pad($matches, 4, '');
    $usernames = array();
    for ($i = 0; $i < count($username); $i ++) {
      # If $after is not empty, there is an invalid character.
      if (!empty($after[$i])) continue;
      array_push($usernames, $username[$i]);
    }
    return $usernames;
  }

  /**
   * Extract all the usernames replied to from the tweet.
   *
   * A reply is an occurrence of a username at the beginning of a tweet.
   *
   * @return  array  The usernames replied to in a tweet.
   */
  public function extractRepliedUsernames() {
    preg_match(self::$patterns['extract_reply'], $this->tweet, $matches);
    return isset($matches[1]) ? $matches[1] : '';
  }

  /**
   * Extracts all the hashtags and the indices they occur at from the tweet.
   *
   * @return  array  The hashtag elements in the tweet.
   */
  public function extractHashtagsWithIndices() {
    preg_match_all(self::$patterns['auto_link_hashtags'], $this->tweet, $matches, PREG_OFFSET_CAPTURE);
    $results = &$matches[3];
    self::fixMultiByteIndices($this->tweet, $matches, $results, array('hashtag'), 1);
    return $results;
  }

  /**
   * Extracts all the URLs and the indices they occur at from the tweet.
   *
   * @return  array  The URLs elements in the tweet.
   */
  public function extractURLsWithIndices() {
    preg_match_all(self::$patterns['valid_url'], $this->tweet, $matches, PREG_OFFSET_CAPTURE);
    $results = &$matches[2];
    self::fixMultiByteIndices($this->tweet, $matches, $results, array('url'), 0);
    # FIXME: Handle extraction of protocol-less domains.
    # https://github.com/twitter/twitter-text-rb/commit/adb6e693b6d003819d615d19219c22d07f114a63
    return $results;
  }

  /**
   * Extracts all the usernames and the indices they occur at from the tweet.
   *
   * @return  array  The username elements in the tweet.
   */
  public function extractMentionedUsernamesWithIndices() {
    preg_match_all(self::$patterns['extract_mentions'], $this->tweet, $matches, PREG_OFFSET_CAPTURE);
    $results = &$matches[2];
    self::fixMultiByteIndices($this->tweet, $matches, $results, array('screen_name'), 1);
    return $results;
  }

  /**
   * Extracts all the usernames and the indices they occur at from the tweet.
   *
   * @return  array  The username elements in the tweet.
   */
  public function extractMentionedUsernamesOrListsWithIndices() {
    preg_match_all(self::$patterns['extract_mentions_or_lists'], $this->tweet, $matches, PREG_OFFSET_CAPTURE);
    $results = array();
    for ($i = 0; $i < count($matches[2]); $i++) {
      $results[] = array($matches[2][$i][0], $matches[3][$i][0], $matches[2][$i][1]);
    }
    self::fixMultiByteIndices($this->tweet, $matches, $results, array('screen_name', 'list_slug'), 1);
    return $results;
  }

  /**
   * Processes an array of matches and fixes up the offsets to support
   * multibyte strings.  This needs to be done due to the state of unicode
   * support in PHP.
   *
   * @param  string  $tweet    The tweet being matched.
   * @param  array   $matches  The matches from the regular expression match.
   * @param  array   $results  The extracted results from the matches.
   * @param  array   $keys     The list of array keys to be added.
   * @param  int     $tweak    An amount to adjust the end index by.
   */
  protected static function fixMultiByteIndices(&$tweet, &$matches, &$results, $keys, $tweak = 1) {
    for ($i = 0; $i < count($results); $i++) {
      # Add the array keys:
      $results[$i] = array_combine(array_merge($keys, array('indices')), $results[$i]);
      # Fix for PREG_OFFSET_CAPTURE returning byte offsets:
      $start = mb_strlen(substr($tweet, 0, $matches[1][$i][1]));
      $start += mb_strlen($matches[1][$i][0]);
      # Determine the multibyte length of the matched string:
      $counter = new MultiByteCounter($results, $i);
// This is not PHP 5.2 compatible
//      $length = array_sum(array_map(function ($key) use (&$results, $i) {
//        return mb_strlen($results[$i][$key]);
//      }, $keys));
      $length = array_sum(array_map(array($counter, 'count_multi_bytes'), $keys));
      # Ensure that the indices array contains the start and end index:
      $results[$i]['indices'] = array($start, $start + $length + $tweak);
    }
  }

}

class MultiByteCounter {
    protected $_results;
    protected $_i;

    public function __construct(&$results, $i) {
        $this->_results = $results;
        $this->_i = $i;
    }

    public function count_multi_bytes($key) {
        return mb_strlen($this->_results[$this->_i][$key]);
    }
}

################################################################################
# vim:et:ft=php:nowrap:sts=2:sw=2:ts=2
