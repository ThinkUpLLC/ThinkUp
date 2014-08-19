<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.tweet_from_id.php
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
 * Help Link
 *
 * Type:     insert<br>
 * Name:     help_link
 * Date:     April 26, 2011
 * Purpose:  Renders a help link.
 * Input:    key
 * Example:  {insert name="help_link" id="api"}
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @version 1.0
 * @param string
 */
function smarty_insert_help_link($params, &$smarty) {
    if (isset($smarty->_tpl_vars['help'][$params['id']])){
        return '<a href="http://thinkup.com/docs/'.$smarty->_tpl_vars['help'][$params['id']].
        '.html" title="Learn more" class="btn btn-xs btn-help">Help <i class="fa fa-question-circle "></i></a>';
    } else {
        return '';
    }
}
