<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<style type="text/css">
body {
	margin: 0;
	border: 0;
	padding: 0;
	font-size: 11pt;
	}
body > h1 {
	margin:0 0 0.5em 0;
	font: 2em sans-serif;
	background-color: #def
	}
body > div {
	padding:2px;
	}
p {
	margin-top: 0;
	}
ins {
	color: green;
	background: #dfd;
	text-decoration: none;
	}
del {
	color: red;
	background: #fdd;
	text-decoration: none;
	}
code {
	font-size: smaller;
	}
#params {
	margin: 1em 0;
	font: 14px sans-serif;
	}
.code {
	margin-left: 2em;
	font: 12px monospace;
	}
.ins {
	background:#dfd;
	}
.del {
	background:#fdd;
	}
.rep {
	color: #008;
	background: #eef;
	}
.panecontainer {
	display: inline-block;
	width: 49.5%;
	vertical-align: top;
	}
.panecontainer > p {
	margin: 0;
	border: 1px solid #bcd;
	border-bottom: none;
	padding: 1px 3px;
	background: #def;
	font: 14px sans-serif
	}
.panecontainer > p + div {
	margin: 0;
	padding: 2px 0 2px 2px;
	border: 1px solid #bcd;
	border-top: none;
	}
.pane {
	margin: 0;
	padding: 0;
	border: 0;
	width: 100%;
	min-height: 20em;
	overflow:auto;
	font: 12px monospace;
	}
#htmldiff.onlyDeletions ins {display:none}
#htmldiff.onlyInsertions del {display:none}
</style>
<title>PHP Fine Diff</title>
</head>
<body>
<a href="https://github.com/gorhill/PHP-FineDiff"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/38ef81f8aca64bb9a64448d0d70f1308ef5341ab/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_darkblue_121621.png"></a>
<h1>PHP Fine Diff</h1>
<div>
<p style="margin-right:5em">This page demonstrate the <a href="finediff-code.php"><code>FineDiff</code></a> class (as in &ldquo;<b>fine</b> granularity <b>diff</b>&rdquo;) I wrote &ndash; starting from scratch &ndash; to generate a <u>lossless</u> (won't eat your line breaks), <u>compact</u> opcodes string listing the sequence of atomic actions (copy/delete/insert) necessary to transform one string into another (thereafter referred as the &ldquo;From&rdquo; and &ldquo;To&rdquo; string). The &ldquo;To&rdquo; string can be rebuilt by running the opcodes string on the &ldquo;From&rdquo; string. The <code>FineDiff</code> class allows to specify the granularity, and up to character-level granularity is possible, in order to generate the smallest diff possible (at the <i>potential</i> cost of increased CPU cycles.)</p>
<p>Typical usage:</p>
<p class="code">
include '<a href="finediff-code.php">finediff.php</a>';<br>
$opcodes = FineDiff::getDiffOpcodes($from_text, $to_text /* , default granularity is set to character */);<br>
// store opcodes for later use...</p>
<p>Later, <code>$to_text</code> can be re-created from <code>$from_text</code> using <code>$opcodes</code> as follow:</p>
<p class="code">
include '<a href="finediff-code.php">finediff.php</a>';<br>
$to_text = FineDiff::renderToTextFromOpcodes($from_text, $opcodes);
</p>
<p>Try it by inserting your own text, or <a href="viewdiff-ex?sample=1">Use sample text</a>, or <a href="viewdiff-ex">Start from scratch</a>, or just use the plain <a href="viewdiff.php">Online diff viewer</a>:</p>
<?php
// http://www.php.net/manual/en/function.get-magic-quotes-gpc.php#82524
function stripslashes_deep(&$value) {
	$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	return $value;
	}
if ( (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || (ini_get('magic_quotes_sybase') && strtolower(ini_get('magic_quotes_sybase'))!="off") ) {
	stripslashes_deep($_GET);
	stripslashes_deep($_POST);
	stripslashes_deep($_REQUEST);
	}

require_once 'finediff.php';

$from = '';
$to = '';
$granularity = 2;
if ( isset($_REQUEST['granularity']) && ctype_digit($_REQUEST['granularity']) ) {
	$granularity = max(min(intval($_REQUEST['granularity']),3),0);
	}
$rendered_diff = '';
if ( !empty($_REQUEST['from']) || !empty($_REQUEST['to'])) {
	if ( !empty($_REQUEST['from']) ) {
		$from = $_REQUEST['from'];
		}
	if ( !empty($_REQUEST['to']) ) {
		$to = $_REQUEST['to'];
		}
	}
else if ( !empty($_REQUEST['sample']) ) { // use sample text?
	// Sample text:
	// http://en.wikipedia.org/w/index.php?title=Universe&action=historysubmit&diff=414830579&oldid=378547111
	$from = file_get_contents('sample_from.txt');
	$to = file_get_contents('sample_to.txt');
	}
$from_len = strlen($from);
$to_len = strlen($to);
$use_stdlib_diff = !empty($_REQUEST['stdlib']) && ctype_digit($_REQUEST['stdlib']) && intval($_REQUEST['stdlib']) === 1;

require_once 'Text/Diff.php';

$start_time = gettimeofday(true);
if ( $use_stdlib_diff ) {
	if ( $granularity < 3 ) {
		$delimiters = array(
			FineDiff::paragraphDelimiters,
			FineDiff::sentenceDelimiters,
			FineDiff::wordDelimiters,
			FineDiff::characterDelimiters
			);
		function extractFragments($text, $delimiter) {
			$text = str_replace(array("\n","\r"), array("\1","\2"), $text);
			$delimiter = str_replace(array("\n","\r"), array("\1","\2"), $delimiter);
			if ( empty($delimiter) ) {
				return str_split($text, 1);
				}
			$fragments = array();
			$start = $end = 0;
			for (;;) {
				$end += strcspn($text, $delimiter, $end);
				$end += strspn($text, $delimiter, $end);
				if ( $end === $start ) {
					break;
					}
				$fragments[] = substr($text, $start, $end - $start);
				$start = $end;
				}
			return $fragments;
			}
		$from_fragments = extractFragments($from, $delimiters[$granularity]);
		$to_fragments = extractFragments($to, $delimiters[$granularity]);
		$diff = new Text_Diff('native', array($from_fragments, $to_fragments));
		$exec_time = sprintf('%.3f sec', gettimeofday(true) - $start_time);
		$edits = array();
		ob_start();
		foreach ( $diff->getDiff() as $edit ) {
			if ( $edit instanceof Text_Diff_Op_copy ) {
				$orig = str_replace(array("\1","\2"), array("\n","\r"), implode('', $edit->orig));
				$edits[] = new fineDiffCopyOp(strlen($orig));
				echo htmlentities($orig);
				}
			else if ( $edit instanceof Text_Diff_Op_delete ) {
				$orig = str_replace(array("\1","\2"), array("\n","\r"), implode('', $edit->orig));
				$edits[] = new fineDiffDeleteOp(strlen($orig));
				echo '<del>', htmlentities($orig), '</del>';
				}
			else if ( $edit instanceof Text_Diff_Op_add ) {
				$final = str_replace(array("\1","\2"), array("\n","\r"), implode('', $edit->final));
				$edits[] = new fineDiffInsertOp($final);
				echo '<ins>', htmlentities($final), '</ins>';
				}
			else if ( $edit instanceof Text_Diff_Op_change ) {
				$orig = str_replace(array("\1","\2"), array("\n","\r"), implode('', $edit->orig));
				$final = str_replace(array("\1","\2"), array("\n","\r"), implode('', $edit->final));
				$edits[] = new fineDiffReplaceOp(strlen($orig), $final);
				echo '<del>', htmlentities($orig), '</del>';
				echo '<ins>', htmlentities($final), '</ins>';
				}
			}
		$rendered_diff = ob_get_clean();
		$rendering_time = sprintf('%.3f sec', gettimeofday(true) - $start_time);
		}
	// character-level granularity not allowed
	else {
		$edits = false;
		$exec_time = '?';
		$rendering_time = '?';
		$rendered_diff = '<span style="color:gray">Character-level granularity not allowed when using <code>Text_Diff</code>, due to performance issues.</span>';
		}
	}
else {
	$granularityStacks = array(
		FineDiff::$paragraphGranularity,
		FineDiff::$sentenceGranularity,
		FineDiff::$wordGranularity,
		FineDiff::$characterGranularity
		);

	$diff = new FineDiff($from, $to, $granularityStacks[$granularity]);
	$edits = $diff->getOps();
	$exec_time = sprintf('%.3f sec', gettimeofday(true) - $start_time);
	$rendered_diff = $diff->renderDiffToHTML();
	$rendering_time = sprintf('%.3f sec', gettimeofday(true) - $start_time);
	}

if ( $edits !== false ) {
	$opcodes = array();
	$opcodes_len = 0;
	foreach ( $edits as $edit ) {
		$opcode = $edit->getOpcode();
		$opcodes_len += strlen($opcode);
		$opcode = htmlentities($opcode);
		if ( $edit instanceof FineDiffCopyOp ) {
			$opcodes[] = "{$opcode}";
			}
		else if ( $edit instanceof FineDiffDeleteOp ) {
			$opcodes[] = "<span class=\"del\">{$opcode}</span>";
			}
		else if ( $edit instanceof FineDiffInsertOp ) {
			$opcodes[] = "<span class=\"ins\">{$opcode}</span>";
			}
		else /* if ( $edit instanceof FineDiffReplaceOp ) */ {
			$opcodes[] = "<span class=\"rep\">{$opcode}</span>";
			}
		}
	$opcodes = implode("", $opcodes);
	$opcodes_len = sprintf('%d bytes (%.1f %% of &quot;To&quot;)', $opcodes_len, $to_len ? $opcodes_len * 100 / $to_len : 0);
	}
else {
	$opcodes = '?';
	$opcodes_len = '?';
	}
?>
<form action="viewdiff-ex.php" method="post">
<div class="panecontainer"><p>From:</p><div><textarea name="from" class="pane"><?php echo htmlentities($from); ?></textarea></div></div>
<div class="panecontainer"><p>To:</p><div><textarea name="to" class="pane"><?php echo htmlentities($to); ?></textarea></div></div>
<p id="params">Granularity:<input name="granularity" type="radio" value="0"<?php if ( $granularity === 0 ) { echo ' checked="checked"'; } ?>>&thinsp;Paragraph/lines&ensp;<input name="granularity" type="radio" value="1"<?php if ( $granularity === 1 ) { echo ' checked="checked"'; } ?>>&thinsp;Sentence&ensp;<input name="granularity" type="radio" value="2"<?php if ( $granularity === 2 ) { echo ' checked="checked"'; } ?>>&thinsp;Word&ensp;<input name="granularity" type="radio" value="3"<?php if ( $granularity === 3 ) { echo ' checked="checked"'; } ?>>&thinsp;Character&emsp;<!-- <input name="XDEBUG_PROFILE" type="hidden" value=""> --><input type="submit" value="Compute diff">&emsp;<input name="stdlib" type="checkbox" value="1"<?php if ( $use_stdlib_diff ) { echo ' checked="checked"'; } ?>><a href="http://pear.php.net/package/Text_Diff/"><code>Text_Diff</code></a> lib (for comparison purpose) <sup style="font-size:x-small"><a href="#notes">see notes</a></sup></p>
</form>
<div class="panecontainer"><p>Diff stats:</p><div><div class="pane">
<b>Diff execution time:</b> <?php echo $exec_time; ?><br>
<b>Diff execution + rendering time:</b> <?php echo $rendering_time; ?><br>
<b>&quot;From&quot; size:</b> <?php echo $from_len; ?> bytes<br>
<b>&quot;To&quot; size:</b> <?php echo $to_len; ?> bytes<br>
<b>Diff opcodes size:</b> <?php echo $opcodes_len; ?><br>
<b>Diff opcodes (<span style="border:1px solid #ccc;display:inline-block;width:16px">&nbsp;</span>=copy, <span class="del" style="display:inline-block;width:16px">&nbsp;</span>=delete, <span class="ins" style="display:inline-block;width:16px">&nbsp;</span>=insert, <span class="rep" style="display:inline-block;width:16px">&nbsp;</span>=replace):</b>
<div style="margin:2px 0 2px 0;border:0;border-top:1px dotted #aaa;padding-top:4px;word-wrap:break-word"><?php echo $opcodes; ?></div>
</div></div></div>
<div class="panecontainer"><p>Rendered Diff:&emsp;<span style="font-size:smaller">Show <input type="radio" name="htmldiffshow" onclick="setHTMLDiffVisibility('deletions');">Deletions only&ensp;<input type="radio" name="htmldiffshow" checked="checked" onclick="setHTMLDiffVisibility();">All&ensp;<input type="radio" name="htmldiffshow" onclick="setHTMLDiffVisibility('insertions');">Insertions only</span></p>
	<div id="htmldiff">
		<div class="pane" style="white-space:pre-wrap"><?php echo $rendered_diff; ?></div>
		</div>
	</div>
<script type="text/javascript">
<!--
function setHTMLDiffVisibility(what) {
	var htmldiffEl = document.getElementById('htmldiff');
	if ( what === 'deletions' ) {
		htmldiffEl.className = 'onlyDeletions';
		}
	else if ( what === 'insertions' ) {
		htmldiffEl.className = 'onlyInsertions';
		}
	else {
		htmldiffEl.className = '';
		}
	}
// -->
</script>
</div>
<div style="margin:0.5em 0;border-top:1px solid #ccc;height:2px"></div>
<div id="notes" style="font:11px sans-serif"><h3 style="margin-top:0">Notes</h3>
	<p>The PHP-based engine of <code><a href="http://pear.php.net/package/Text_Diff/">Text_Diff</a></code> is forced, in order to meaningfully compare results with PHP-based <code>FineDiff</code>. <code>Text_Diff</code> is naturally geared toward line-level granularity, and to compute diff for a higher granularity (sequences, words, characters), line break characters (\n, \r) are replaced in order to avoid having <code>Text_Diff</code> from eating our line breaks &mdash; so extra steps are required.</p>
	<p><code>FineDiff</code> is natively better equipped to generate diff at granularity higher than line levels. An example of this is that using the above built-in sample text, for word and character-level granularity, <code>FineDiff</code> roughly executes in 25 ms and 30 ms, respectively, while <code>Text_Diff</code> roughly executes in 75 ms and 6.5 seconds, respectively (on my development computer, a run of the mill Intel i5 core desktop computer).</p>
	<p>If you wish to comment on this page, head to the associated blog entry: <a href="http://www.raymondhill.net/blog/?p=441">FineDiff, a character-level diff algorithm in PHP</a></p>
	</div>
</body>
</html>
