<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.color_html_email_links.php
 *
 * Copyright (c) 2009-2014 Matt Jacobs
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
 * Smarty link URLs plugin
 *
 * Type:     modifier<br>
 * Name:     color_html_email_links<br>
 * Date:     March 4, 2014
 * Purpose:  Creates a linked URL
 * Input:    post text
 * Example:  {$post->post_text|color_html_email_links}
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2014 Matt Jacobs
 * @author   Matt Jacobs
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_color_html_email_links($text, $color='#46bcff') {
 return preg_replace('/(<a)/', "<a style=\"color:$color;text-decoration:none;\"", $text);
}
