<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.link_usernames.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 */
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty replace accented characters plugin
 *
 * Type:     modifier
 * Name:     replace_accented_characters
 * Date:     February 6, 2014
 * Purpose:  Replaces accented characters with an unaccented version
 * Input:    text
 * Example:  {$full_name|replace_accented_characters}
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Matt Jacobs
 * @author   Matt Jacobs
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_replace_accented_characters($text) {
  $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
  $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
  $stringWithReplacedCharacters = str_replace($search, $replace, $text);
  return $stringWithReplacedCharacters;
}
