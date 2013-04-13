<?php

include 'functions.php';

initialize();
$info_text = "がんばって！";
$prev_voc_index = -1;

# add word
if(isset($_POST['add_voc']) && $_POST['add_voc'] == '1') {
	add_voc($_POST['jp'], $_POST['ger'], $_POST['i'], $_POST['kanji']);
	$info_text = 'added '.$_POST['jp'].' to vocabulary';
	}
$vocs_array = get_vocs();
$vocs_disabled = get_vocs('disabled');
$vocs_count_enabled = ($vocs_array ? count($vocs_array) : 0);
$vocs_count_disabled = ($vocs_disabled ? count($vocs_disabled) : 0);
$mode = file_get_contents('mode');

# toggle mode
if(isset($_POST['toggle_mode']) && $_POST['toggle_mode'] == '1') {
	$mode = ($mode+1)%3;
	file_put_contents('mode', $mode);
	}

# check answer
if(isset($_POST['answer']) && isset($_POST['disp_lang']) && isset($_POST['voc_index']) && $_POST['voc_index'] > -1) {
	// check & answer
	$d_lang = $_POST['disp_lang'];
	$wanted_lang = ($d_lang=='jp' ? 'ger' : 'jp');
	$prev_voc_index = $_POST['voc_index'];
	$given_answer = $_POST['answer'];
	$right_answer = $vocs_array[$prev_voc_index]->$wanted_lang;
	$prevkanji = (strlen($vocs_array[$prev_voc_index]->kanji)>0 ? $vocs_array[$prev_voc_index]->kanji : '<em class="grey">-</em>' );
	if($given_answer == $right_answer) {
		$info_text = '<span class="green">correct</span>&emsp;'.$vocs_array[$prev_voc_index]->$d_lang.' = '.kanji_if_kana($vocs_array[$prev_voc_index], $wanted_lang, 'prevkanji');
		if(strlen($vocs_array[$prev_voc_index]->i) > 0) $info_text .= ' <span class="grey">('.$vocs_array[$prev_voc_index]->i.')</span>';
		}
	else {
		$info_text = '<span class="red">wrong</span>&emsp;'.$vocs_array[$prev_voc_index]->$d_lang.' = '.kanji_if_kana($vocs_array[$prev_voc_index], $wanted_lang, 'prevkanji').' (<span class="strike">'.$given_answer.'</span>)';
		}
	// note stats
	$rw = ($given_answer == $right_answer ? 'r' : 'w');
	$ft = ($d_lang=='jp' ? 'f' : 't');
	$attr = $rw.$ft;
	$tmp = $vocs_array[$prev_voc_index]->$attr;
	$tmp++;
	$vocs_array[$prev_voc_index]->$attr = $tmp;
	
	update_enabled_vocs($vocs_array);
	}

# set mode
switch($mode) {
	case 0:
		$disp_lang = rand(0, 1) ? 'jp' : 'ger';
		$mode_indicator = 'jp <-> de';
		break;
	case 1:
		$disp_lang = 'jp';
		$mode_indicator = 'jp --> de';
		break;
	case 2:
		$disp_lang = 'ger';
		$mode_indicator = 'de --> jp';
		break;
	default:
		die('unsupported mode given');
	}
# choose word to translate
if($vocs_array) {
	$cv_tmp_arr = get_bad_voc_and_index($vocs_array, $mode, $prev_voc_index);
	$curr_voc_index = $cv_tmp_arr[0];
	$curr_voc = $cv_tmp_arr[1];

	$disp_text = kanji_if_kana($curr_voc, $disp_lang, 'currkanji');
	if($disp_lang == 'jp') {
		$voc_info = '';
		$currkanji = (strlen($curr_voc->kanji)>0 ? $curr_voc->kanji : '<em class="grey">-</em>' );
		}
	else {
		$voc_info = $curr_voc->i;
		$currkanji = '';
		}
	}
else {
	$curr_voc_index = -1;
	$curr_voc = get_empty_voc();
	$info_text = 'vocabulary is empty';
	$disp_text = '-';
	$voc_info = '-';
	}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf8">
<link rel="stylesheet" type="text/css" href="style.css" />
<script>
// add from
sessionStorage.setItem('add_form', 0);
function toggleAdd() {
	var af_status = sessionStorage.getItem('add_form');
	if(af_status == 0) {
		document.getElementById('add_form').setAttribute('style', 'display: block;');
		sessionStorage.setItem('add_form', 1);
		}
	else {
		document.getElementById('add_form').setAttribute('style', 'display: none;');
		sessionStorage.setItem('add_form', 0);
		}
	}

// question mode
function toggleMode() {
	document.getElementById('mode_form').submit();
	}

// kanji
function showById(i) {
	document.getElementById(i).style.display = 'block';
	}
function hideById(i) {
	document.getElementById(i).style.display = 'none';
	}
function kanjiBlockLetterById(i) {
	document.getElementById(i).classList.remove('kanjistrokeorder');
	document.getElementById(i).classList.add('kanjiblockletter');
	}
function kanjiStrokeOrderById(i) {
	document.getElementById(i).classList.remove('kanjiblockletter');
	document.getElementById(i).classList.add('kanjistrokeorder');
	}
</script>
</head>
<body>
<div id="main">
	<div id="info">
		<p><?php echo $info_text; ?></p>
		<div id="prevkanji" class="kanji" title="hide kanji" onclick="hideById('prevkanji');" onmouseout="kanjiBlockLetterById('prevkanjispan');" onmouseover="kanjiStrokeOrderById('prevkanjispan');">
			<span class="kanjiblockletter">&thinsp;</span>
			<span id="prevkanjispan" class="kanjistrokeorder"><?php echo $prevkanji; ?></span>
			<span class="kanjiblockletter">&thinsp;</span>
		</div>
	</div>
	<div id="voc">
		<p><?php echo $disp_text; ?></p>
		<p id="voc_info"><?php echo $voc_info; ?>&nbsp;</p>
		<div id="currkanji" class="kanji" title="hide kanji" onclick="hideById('currkanji');" onmouseout="kanjiBlockLetterById('currkanjispan');" onmouseover="kanjiStrokeOrderById('currkanjispan');">
			<span class="kanjiblockletter">&thinsp;</span>
			<span id="currkanjispan" class="kanjistrokeorder"><?php echo $currkanji; ?></span>
			<span class="kanjiblockletter">&thinsp;</span>
		</div>
		<form id="answer_form" method="POST" action="">
			<input id="answer" type="text" name="answer" autocomplete="off" />
			<input type="hidden" name="disp_lang" value="<?php echo $disp_lang; ?>" />
			<input type="hidden" name="voc_index" value="<?php echo $curr_voc_index; ?>" />
		</form>
	</div>
	<div id="stats">
		<?php
			echo '<p>stats for <em>'.$disp_text.'</em>:</p>'.
				'<p>de -> jp: <span class="green">'.$curr_voc->rt.'</span> / <span class="red">'.$curr_voc->wt.'</span></p>'.
				'<p>jp -> de: <span class="green">'.$curr_voc->rf.'</span> / <span class="red">'.$curr_voc->wf.'</span></p>';
		?>
	</div>
	<div id="add">
		<p><strong>mode:</strong>&emsp;<span id="mode_indicator"><?php echo $mode_indicator; ?></span>&emsp;(<a href="" onclick="toggleMode();return false;">toggle</a>)</p>
		<form id="mode_form" method="POST" action="" style="display: none;">
			<input type="hidden" name="toggle_mode" value="1" />
		</form>
		<p><a href="edit.php">edit vocabulary</a> <span class="grey">(&thinsp;<?php echo $vocs_count_enabled.'&thinsp;/&thinsp;'.$vocs_count_disabled; ?>&thinsp;)</span></p>
		<p><a href="" onclick="toggleAdd();return false;">add word to vocabulary</a></p>
		<form id="add_form" method="POST" action="" style="display: none;">
			<table>
				<tr>
					<td><p>kana</p></td>
					<td><input type="text" name="jp" autocomplete="off" /></td>
				</tr>
				<tr>
					<td><p>kanji</p></td>
					<td><input type="text" name="kanji" autocomplete="off" /></td>
				</tr>
				<tr>
					<td><p>german</td>
					<td><input type="text" name="ger" autocomplete="off" /></td>
				</tr>
				<tr>
					<td><p>additional info</td>
					<td><input type="text" name="i" autocomplete="off" /></td>
				</tr>
				<tr>
					<td colspan="2"><input type="hidden" name="add_voc" value="1" />
					<input type="submit" value="add" /></td>
				</tr>
			</table>
		</form>
	<div>
<div>
<script>
document.getElementById('answer').focus();
</script>
</body>
</html>
