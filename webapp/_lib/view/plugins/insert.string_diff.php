<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/insert.string_diff.php
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
 * Smarty string diff plugin
 *
 * Type:     insert<br>
 * Name:     string_diff<br>
 * Date:     August 26, 2014
 * Purpose:  Creates an HTML diff of two text strings
 * Input:    two strings
 * Example:  {insert name="string_diff" from_text="foo" to_text="bar"}
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2014 Matt Jacobs
 * @author   Matt Jacobs
 * @version 1.0
 */
function smarty_insert_string_diff($params, &$smarty) {
  if (empty($params['from_text']) || empty($params['to_text'])) {
    trigger_error("Missing 'from_text' or 'to_text' paramaters");
    return;
  }

  require_once THINKUP_WEBAPP_PATH.'_lib/extlib/FineDiff/finediff.php';
  $opcodes = FineDiff::getDiffOpcodes($params['from_text'], $params['to_text'], $granularityStack = FineDiff::$wordGranularity);
  $diff = FineDiff::renderDiffToHTMLFromOpcodes($params['from_text'], $opcodes);

  if (isset($params['is_email']) && $params['is_email']) {
    $diff = str_replace('<ins', '<ins style="background: #e4f9e8; text-decoration: none;"', $diff);
    $diff = str_replace('<del', '<del style="background: #f8d9dd; color: #dc4154;"', $diff);
  }

  return $diff;
}