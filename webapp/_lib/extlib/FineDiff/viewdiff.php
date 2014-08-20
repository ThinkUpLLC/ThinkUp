<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<style type="text/css">
body {margin:0;border:0;padding:0;font:11pt sans-serif}
body > h1 {margin:0 0 0.5em 0;font:2em sans-serif;background-color:#def}
body > div {padding:2px}
p {margin-top:0}
ins {color:green;background:#dfd;text-decoration:none}
del {color:red;background:#fdd;text-decoration:none}
#params {margin:1em 0;font: 14px sans-serif}
.panecontainer > p {margin:0;border:1px solid #bcd;border-bottom:none;padding:1px 3px;background:#def;font:14px sans-serif}
.panecontainer > p + div {margin:0;padding:2px 0 2px 2px;border:1px solid #bcd;border-top:none}
.pane {margin:0;padding:0;border:0;width:100%;min-height:20em;overflow:auto;font:12px monospace}
#htmldiff {color:gray}
#htmldiff.onlyDeletions ins {display:none}
#htmldiff.onlyInsertions del {display:none}
</style>
<title>PHP Fine Diff: Online Diff Viewer</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<a href="https://github.com/gorhill/PHP-FineDiff"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/38ef81f8aca64bb9a64448d0d70f1308ef5341ab/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_darkblue_121621.png"></a>
<h1>PHP Fine Diff: Online Diff Viewer</h1>
<div>
<?php
// http://www.php.net/manual/en/function.get-magic-quotes-gpc.php#82524
function stripslashes_deep(&$value) {
	$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	return $value;
	}
if ( (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || (ini_get('magic_quotes_sybase') && strtolower(ini_get('magic_quotes_sybase'))!="off") ) {
	stripslashes_deep($_GET);
	stripslashes_deep($_POST);
	}

include 'finediff.php';

$cache_lo_water_mark = 900;
$cache_hi_water_mark = 1100;
$compressed_serialized_filename_extension = '.store.gz';

$granularity = 2;
$from_text = '';
$to_text = '';
$diff_opcodes = '';
$diff_opcodes_len = 0;
$data_key = '';

$start_time = gettimeofday(true);

// restore from cache
if ( isset($_GET['data']) ) {
	if ( ctype_alnum($_GET['data']) ) {
		$filename = "{$_GET['data']}{$compressed_serialized_filename_extension}";
		$compressed_serialized_data = @file_get_contents("./cache/{$filename}");
		if ( $compressed_serialized_data !== false ) {
			@touch("./cache/{$filename}");
			$data_from_serialization = unserialize(gzuncompress($compressed_serialized_data));
			$granularity = $data_from_serialization['granularity'];
			$from_text = $data_from_serialization['from_text'];
			$diff_opcodes = $data_from_serialization['diff_opcodes'];
			$diff_opcodes_len = strlen($diff_opcodes);
			$to_text = FineDiff::renderToTextFromOpcodes($from_text, $diff_opcodes);
			$data_key = $data_from_serialization['data_key'];
			}
		else {
			echo '<p style="font-size:smaller">The page you are looking for has expired.</p>', "\n";
			}
		}
	$exec_time = gettimeofday(true) - $start_time;
	}
// new diff
else {
	if ( isset($_POST['granularity']) && ctype_digit($_POST['granularity']) ) {
		$granularity = max(min(intval($_POST['granularity']),3),0);
		}
	if ( !empty($_POST['from']) || !empty($_POST['to'])) {
		if ( !empty($_POST['from']) ) {
			$from_text = $_POST['from'];
			}
		if ( !empty($_POST['to']) ) {
			$to_text = $_POST['to'];
			}
		}
	// limit input
	$from_text = substr($from_text, 0, 1024*100);
	$to_text = substr($to_text, 0, 1024*100);

	// ensure input is suitable for diff
	$from_text = mb_convert_encoding($from_text, 'HTML-ENTITIES', 'UTF-8');
	$to_text = mb_convert_encoding($to_text, 'HTML-ENTITIES', 'UTF-8');

	$granularityStacks = array(
		FineDiff::$paragraphGranularity,
		FineDiff::$sentenceGranularity,
		FineDiff::$wordGranularity,
		FineDiff::$characterGranularity
		);
	$diff_opcodes = FineDiff::getDiffOpcodes($from_text, $to_text, $granularityStacks[$granularity]);
	$diff_opcodes_len = strlen($diff_opcodes);
	$exec_time = gettimeofday(true) - $start_time;
	if ( $diff_opcodes_len ) {
		$data_key = sha1(serialize(array('granularity' => $granularity, 'from_text' => $from_text, 'diff_opcodes' => $diff_opcodes)));
		$filename = "{$data_key}{$compressed_serialized_filename_extension}";
		if ( !file_exists("./cache/{$filename}") ) {
			// purge cache if too many files
			if ( !(time() % 100) ) {
				$files = glob("./cache/*{$compressed_serialized_filename_extension}");
				$num_files = $files ? count($files) : 0;
				if ( $num_files > $cache_hi_water_mark ) {
					$sorted_files = array();
					foreach ( $files as $file ) {
						$sorted_files[strval(@filemtime("./cache/{$file}")).$file] = $file;
						}
					ksort($sorted_files);
					foreach ( $sorted_files as $file ) {
						@unlink("./cache/{$file}");
						$num_files -= 1;
						if ( $num_files < $cache_lo_water_mark ) {
							break;
							}
						}
					}
				}
			// save diff in cache
			$data_to_serialize = array(
				'granularity' => $granularity,
				'from_text' => $from_text,
				'diff_opcodes' => $diff_opcodes,
				'data_key' => $data_key,
				);
			$serialized_data = serialize($data_to_serialize);
			@file_put_contents("./cache/{$filename}", gzcompress($serialized_data));
			@chmod("./cache/{$filename}", 0666);
			}
		}
	}

$rendered_diff = FineDiff::renderDiffToHTMLFromOpcodes($from_text, $diff_opcodes);
$from_len = strlen($from_text);
$to_len = strlen($to_text);

if ( !empty($data_key) ) {
	echo '<p style="margin-right:8em;font-size:smaller">Tempolink: <a href="viewdiff.php?data=', $data_key, '">http://', $_SERVER['HTTP_HOST'], '/viewdiff.php?data=', $data_key, '</a> <span style="color:#aaa">(This link is not viewable by others, unless it has been explicitly shared by the creator. This link will exist for a limited period of time, which depends on how often it is visited.)</span></p>', "\n";
	}
?>
<div class="panecontainer" style="width:99%"><p>Diff <span style="color:gray">(diff: <?php printf('%.3f', $exec_time); ?> seconds, diff len: <?php echo $diff_opcodes_len; ?> chars)</span>&emsp;/&emsp;Show <input type="radio" name="htmldiffshow" onclick="setHTMLDiffVisibility('deletions');">Deletions only&ensp;<input type="radio" name="htmldiffshow" checked="checked" onclick="setHTMLDiffVisibility();">All&ensp;<input type="radio" name="htmldiffshow" onclick="setHTMLDiffVisibility('insertions');">Insertions only</p><div><div id="htmldiff" class="pane" style="white-space:pre-wrap"><?php
echo $rendered_diff; ?></div></div>
</div>
<form action="viewdiff.php" method="post">
<p style="margin:1em 0 0.5em 0">Enter text to diff below:</p>
<div class="panecontainer" style="display:inline-block;width:49.5%"><p>From</p><div><textarea name="from" class="pane"><?php echo htmlentities($from_text, ENT_QUOTES, 'UTF-8'); ?></textarea></div></div>
<div class="panecontainer" style="display:inline-block;width:49.5%"><p>To</p><div><textarea name="to" class="pane"><?php echo htmlentities($to_text, ENT_QUOTES, 'UTF-8'); ?></textarea></div></div>
<p id="params">Granularity:<input name="granularity" type="radio" value="0"<?php if ( $granularity === 0 ) { echo ' checked="checked"'; } ?>>&thinsp;Paragraph/lines&ensp;<input name="granularity" type="radio" value="1"<?php if ( $granularity === 1 ) { echo ' checked="checked"'; } ?>>&thinsp;Sentence&ensp;<input name="granularity" type="radio" value="2"<?php if ( $granularity === 2 ) { echo ' checked="checked"'; } ?>>&thinsp;Word&ensp;<input name="granularity" type="radio" value="3"<?php if ( $granularity === 3 ) { echo ' checked="checked"'; } ?>>&thinsp;Character&emsp;<input type="submit" value="View diff">&emsp;<a href="viewdiff.php"><button>Clear all</button></a></p>
</form>
<p style="margin-top:1em"><a href="viewdiff-ex.php">Go to main page</a></p>
<script type="text/javascript">
<!--
function setHTMLDiffVisibility(what) {
	var htmldiffEl = document.getElementById('htmldiff'),
		className = htmldiffEl.className;
	className = className.replace(/\bonly(Insertions|Deletions)\b/g, '').replace(/\s{2,}/g, ' ').replace(/\s+$/, '').replace(/^\s+/, '');
	if ( what === 'deletions' ) {
		htmldiffEl.className = className + ' onlyDeletions';
		}
	else if ( what === 'insertions' ) {
		htmldiffEl.className = className + ' onlyInsertions';
		}
	else {
		htmldiffEl.className = className;
		}
	}
// -->
</script>
</body>
</html>
