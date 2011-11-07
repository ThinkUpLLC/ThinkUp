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
 * Twitter Autolink Class
 *
 * Parses tweets and generates HTML anchor tags around URLs, usernames,
 * username/list pairs and hashtags.
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
class Twitter_Autolink extends Twitter_Regex {

  /**
   * CSS class for auto-linked URLs.
   *
   * @var  string
   */
  protected $class_url = 'url';

  /**
   * CSS class for auto-linked username URLs.
   *
   * @var  string
   */
  protected $class_user = 'username';

  /**
   * CSS class for auto-linked list URLs.
   *
   * @var  string
   */
  protected $class_list = 'list';

  /**
   * CSS class for auto-linked hashtag URLs.
   *
   * @var  string
   */
  protected $class_hash = 'hashtag';

  /**
   * URL base for username links (the username without the @ will be appended).
   *
   * @var  string
   */
  protected $url_base_user = 'http://twitter.com/';

  /**
   * URL base for list links (the username/list without the @ will be appended).
   *
   * @var  string
   */
  protected $url_base_list = 'http://twitter.com/';

  /**
   * URL base for hashtag links (the hashtag without the # will be appended).
   *
   * @var  string
   */
  protected $url_base_hash = 'http://twitter.com/#!/search?q=%23';

  /**
   * Whether to include the value 'nofollow' in the 'rel' attribute.
   *
   * @var  bool
   */
  protected $nofollow = true;

  /**
   * Whether to include the value 'external' in the 'rel' attribute.
   *
   * Often this is used to be matched on in JavaScript for dynamically adding
   * the 'target' attribute which is deprecated in HTML 4.01.  In HTML 5 it has
   * been undeprecated and thus the 'target' attribute can be used.  If this is
   * set to false then the 'target' attribute will be output.
   *
   * @var  bool
   */
  protected $external = true;

  /**
   * The scope to open the link in.
   *
   * Support for the 'target' attribute was deprecated in HTML 4.01 but has
   * since been reinstated in HTML 5.  To output the 'target' attribute you
   * must disable the adding of the string 'external' to the 'rel' attribute.
   *
   * @var  string
   */
  protected $target = '_blank';

  /**
   * Provides fluent method chaining.
   *
   * @param  string  $tweet        The tweet to be converted.
   * @param  bool    $full_encode  Whether to encode all special characters.
   *
   * @see  __construct()
   *
   * @return  Twitter_Autolink
   */
  public static function create($tweet, $full_encode = false) {
    return new self($tweet, $full_encode);
  }

  /**
   * Reads in a tweet to be parsed and converted to contain links.
   *
   * As the intent is to produce links and output the modified tweet to the
   * user, we take this opportunity to ensure that we escape user input.
   *
   * @see  htmlspecialchars()
   *
   * @param  string  $tweet        The tweet to be converted.
   * @param  bool    $escape       Whether to escape the tweet (default: true).
   * @param  bool    $full_encode  Whether to encode all special characters.
   */
  public function __construct($tweet, $escape = true, $full_encode = false) {
    if ($escape) {
      if ($full_encode) {
        parent::__construct(htmlentities($tweet, ENT_QUOTES, 'UTF-8', false));
      } else {
        parent::__construct(htmlspecialchars($tweet, ENT_QUOTES, 'UTF-8', false));
      }
    } else {
      parent::__construct($tweet);
    }
  }

  /**
   * CSS class for auto-linked URLs.
   *
   * @return  string  CSS class for URL links.
   */
  public function getURLClass() {
    return $this->class_url;
  }

  /**
   * CSS class for auto-linked URLs.
   *
   * @param  string  $v  CSS class for URL links.
   *
   * @return  Twitter_Autolink  Fluid method chaining.
   */
  public function setURLClass($v) {
    $this->class_url = trim($v);
    return $this;
  }

  /**
   * CSS class for auto-linked username URLs.
   *
   * @return  string  CSS class for username links.
   */
  public function getUsernameClass() {
    return $this->class_user;
  }

  /**
   * CSS class for auto-linked username URLs.
   *
   * @param  string  $v  CSS class for username links.
   *
   * @return  Twitter_Autolink  Fluid method chaining.
   */
  public function setUsernameClass($v) {
    $this->class_user = trim($v);
    return $this;
  }

  /**
   * CSS class for auto-linked username/list URLs.
   *
   * @return  string  CSS class for username/list links.
   */
  public function getListClass() {
    return $this->class_list;
  }

  /**
   * CSS class for auto-linked username/list URLs.
   *
   * @param  string  $v  CSS class for username/list links.
   *
   * @return  Twitter_Autolink  Fluid method chaining.
   */
  public function setListClass($v) {
    $this->class_list = trim($v);
    return $this;
  }

  /**
   * CSS class for auto-linked hashtag URLs.
   *
   * @return  string  CSS class for hashtag links.
   */
  public function getHashtagClass() {
    return $this->class_hash;
  }

  /**
   * CSS class for auto-linked hashtag URLs.
   *
   * @param  string  $v  CSS class for hashtag links.
   *
   * @return  Twitter_Autolink  Fluid method chaining.
   */
  public function setHashtagClass($v) {
    $this->class_hash = trim($v);
    return $this;
  }

  /**
   * Whether to include the value 'nofollow' in the 'rel' attribute.
   *
   * @return  bool  Whether to add 'nofollow' to the 'rel' attribute.
   */
  public function getNoFollow() {
    return $this->nofollow;
  }

  /**
   * Whether to include the value 'nofollow' in the 'rel' attribute.
   *
   * @param  bool  $v  The value to add to the 'target' attribute.
   *
   * @return  Twitter_Autolink  Fluid method chaining.
   */
  public function setNoFollow($v) {
    $this->nofollow = $v;
    return $this;
  }

  /**
   * Whether to include the value 'external' in the 'rel' attribute.
   *
   * Often this is used to be matched on in JavaScript for dynamically adding
   * the 'target' attribute which is deprecated in HTML 4.01.  In HTML 5 it has
   * been undeprecated and thus the 'target' attribute can be used.  If this is
   * set to false then the 'target' attribute will be output.
   *
   * @return  bool  Whether to add 'external' to the 'rel' attribute.
   */
  public function getExternal() {
    return $this->external;
  }

  /**
   * Whether to include the value 'external' in the 'rel' attribute.
   *
   * Often this is used to be matched on in JavaScript for dynamically adding
   * the 'target' attribute which is deprecated in HTML 4.01.  In HTML 5 it has
   * been undeprecated and thus the 'target' attribute can be used.  If this is
   * set to false then the 'target' attribute will be output.
   *
   * @param  bool  $v  The value to add to the 'target' attribute.
   *
   * @return  Twitter_Autolink  Fluid method chaining.
   */
  public function setExternal($v) {
    $this->external = $v;
    return $this;
  }

  /**
   * The scope to open the link in.
   *
   * Support for the 'target' attribute was deprecated in HTML 4.01 but has
   * since been reinstated in HTML 5.  To output the 'target' attribute you
   * must disable the adding of the string 'external' to the 'rel' attribute.
   *
   * @return  string  The value to add to the 'target' attribute.
   */
  public function getTarget() {
    return $this->target;
  }

  /**
   * The scope to open the link in.
   *
   * Support for the 'target' attribute was deprecated in HTML 4.01 but has
   * since been reinstated in HTML 5.  To output the 'target' attribute you
   * must disable the adding of the string 'external' to the 'rel' attribute.
   *
   * @param  string  $v  The value to add to the 'target' attribute.
   *
   * @return  Twitter_Autolink  Fluid method chaining.
   */
  public function setTarget($v) {
    $this->target = trim($v);
    return $this;
  }

  /**
   * Adds links to all elements in the tweet.
   *
   * @return  string  The modified tweet.
   */
  public function addLinks() {
    $original = $this->tweet;
    $this->tweet = $this->addLinksToHashtags();
    $this->tweet = $this->addLinksToURLs();
    $this->tweet = $this->addLinksToUsernamesAndLists();
    $modified = $this->tweet;
    $this->tweet = $original;
    return $modified;
  }

  /**
   * Adds links to hashtag elements in the tweet.
   *
   * @return  string  The modified tweet.
   */
  public function addLinksToHashtags() {
    return preg_replace_callback(
      self::$patterns['auto_link_hashtags'],
      array($this, '_addLinksToHashtags'),
      $this->tweet);
  }

  /**
   * Adds links to URL elements in the tweet.
   *
   * @return  string  The modified tweet.
   */
  public function addLinksToURLs() {
    return preg_replace_callback(
      self::$patterns['valid_url'],
      array($this, '_addLinksToURLs'),
      $this->tweet);
  }

  /**
   * Adds links to username/list elements in the tweet.
   *
   * @return  string  The modified tweet.
   */
  public function addLinksToUsernamesAndLists() {
    return preg_replace_callback(
      self::$patterns['auto_link_usernames_or_lists'],
      array($this, '_addLinksToUsernamesAndLists'),
      $this->tweet);
  }

  /**
   * Wraps a tweet element in an HTML anchor tag using the provided URL.
   *
   * This is a helper function to perform the generation of the link.
   *
   * @param  string  $url      The URL to use as the href.
   * @param  string  $class    The CSS class(es) to apply (space separated).
   * @param  string  $element  The tweet element to wrap.
   *
   * @return  string  The tweet element with a link applied.
   */
  protected function wrap($url, $class, $element) {
    $link  = '<a';
    if ($class) $link .= ' class="'.$class.'"';
    $link .= ' href="'.$url.'"';
    $rel = array();
    if ($this->external) $rel[] = 'external';
    if ($this->nofollow) $rel[] = 'nofollow';
    if (!empty($rel)) $link .= ' rel="'.implode(' ', $rel).'"';
    if ($this->target) $link .= ' target="'.$this->target.'"';
    $link .= '>'.$element.'</a>';
    return $link;
  }

  /**
   * Callback used by the method that adds links to hashtags.
   *
   * @see  addLinksToHashtags()
   *
   * @param  array  $matches  The regular expression matches.
   *
   * @return  string  The link-wrapped hashtag.
   */
  protected function _addLinksToHashtags($matches) {
    $replacement = $matches[1];
    $element = $matches[2] . $matches[3];
    $url = $this->url_base_hash . $matches[3];
    $replacement .= $this->wrap($url, $this->class_hash, $element);
    return $replacement;
  }

  /**
   * Callback used by the method that adds links to URLs.
   *
   * @see  addLinksToURLs()
   *
   * @param  array  $matches  The regular expression matches.
   *
   * @return  string  The link-wrapped URL.
   */
  protected function _addLinksToURLs($matches) {
    list($all, $before, $url, $protocol, $domain, $path, $query) = array_pad($matches, 7, '');
    $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8', false);
    if (!$protocol) return $all;
    return $before . $this->wrap($url, $this->class_url, $url);
  }

  /**
   * Callback used by the method that adds links to username/list pairs.
   *
   * @see  addLinksToUsernamesAndLists()
   *
   * @param  array  $matches  The regular expression matches.
   *
   * @return  string  The link-wrapped username/list pair.
   */
  protected function _addLinksToUsernamesAndLists($matches) {
    list($all, $before, $at, $username, $slash_listname, $after) = array_pad($matches, 6, '');
    # If $after is not empty, there is an invalid character.
    if (!empty($slash_listname)) {
      # Replace the list and username
      $element = $username . $slash_listname;
      $class = $this->class_list;
      $url = $this->url_base_list . $element;
    } else {
      if (preg_match(self::$patterns['end_screen_name_match'], $after)) return $all;
      # Replace the username
      $element = $username;
      $class = $this->class_user;
      $url = $this->url_base_user . $element;
    }
    # XXX: Due to use of preg_replace_callback() for multiple replacements in a
    #      single tweet and also as only the match is replaced and we have to
    #      use a look-ahead for $after because there is no equivalent for the
    #      $' (dollar apostrophe) global from Ruby, we MUST NOT append $after.
    return $before . $at . $this->wrap($url, $class, $element);
  }

}

################################################################################
# vim:et:ft=php:nowrap:sts=2:sw=2:ts=2
