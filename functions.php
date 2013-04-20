<?php

function initialize() {
	$ve = file_exists('voc.json');
	if(!$ve) {
		$vocs_obj = new stdClass();
		$vocs_obj->enabled = array();
		$vocs_obj->disabled = array();
		$json_string = json_encode($vocs_obj);
		$success = file_put_contents('voc.json', $json_string);
		if($success === false) die('Unable to initialize.');
		}
	$me = file_exists('mode');
	if(!$me) {
		$mode_string = '0';
		$success = file_put_contents('mode', $mode_string);
		if($success === false) die('Unable to initialize.');
		}
	$le = file_exists('last_vocs.json');
	if(!$le) {
		$last_vocs_indexes = array();
		$success = file_put_contents('last_vocs.json', $last_vocs_indexes);
		if($success === false) die('Unable to initialize.');
		}
}

function add_voc($jp, $ger, $i="", $kanji="") {
	$json_string = file_get_contents('voc.json');
	$vocs_obj = json_decode($json_string);

	$new_voc_obj = new stdClass();
	$new_voc_obj->jp = $jp; 	# kana
	$new_voc_obj->kanji = $kanji; 	# kanji
	$new_voc_obj->ger = $ger; 	# german
	$new_voc_obj->i = $i;		# additional info
	$new_voc_obj->rf = 0;		# translated right from japanese
	$new_voc_obj->wf = 0;		#            wrong
	$new_voc_obj->rt = 0;		#            right to
	$new_voc_obj->wt = 0;		#            wrong to

	$vocs_obj->enabled[] = $new_voc_obj;
	
	$json_string = json_encode($vocs_obj);
	file_put_contents('voc.json', $json_string);
	}

function get_voc_score($voc, $mode=0) {
	$score = 0;
	switch($mode) {
		case 0:
			$score = ($voc->rf - $voc->wf) + ($voc->rt - $voc->wt);
			break;
		case 1:
			$score = ($voc->rf - $voc->wf);
			break;
		case 2:
			$score = ($voc->rt - $voc->wt);
			break;
		default:
			die('unsupported mode given');
		}
	return $score;
	}

function get_voc_ratio($voc, $mode=0) {
	$right = 0;
	$count = 0;
	switch($mode) {
		case 0:
			$right = $voc->rf + $voc->rt;
			$count = $voc->rf + $voc->wf + $voc->rt + $voc->wt;
			if($count < 10) return 0;
			break;
		case 1:
			$right = $voc->rf;
			$count = $voc->rf + $voc->wf;
			if($count < 5) return 0;
			break;
		case 2:
			$right = $voc->rt;
			$count = $voc->rt + $voc->wt;
			if($count < 5) return 0;
			break;
		default:
			die('unsupported mode given');
		}
	return $right/($count ? $count : 1);
	}

function is_in_last_vocs($voc_index) {
	$json_string = file_get_contents('last_vocs.json');
	$last_vocs_indexes = json_decode($json_string);
	if(!$last_vocs_indexes) return false;
	return in_array($voc_index, $last_vocs_indexes);
	}

function add_to_last_vocs($voc_index) {
	$repetition_prevention = 5;
	$json_string = file_get_contents('last_vocs.json');
	$maybe_null = json_decode($json_string);
	$last_vocs_indexes = ($maybe_null == NULL ? array('I\'m PHP and I\'m stupid.') : $maybe_null);
	if(count($last_vocs_indexes) < $repetition_prevention) {
		array_unshift($last_vocs_indexes, $voc_index);
		}
	else {
		array_pop($last_vocs_indexes);
		array_unshift($last_vocs_indexes, $voc_index);
		}
	$json_string = json_encode($last_vocs_indexes);
	file_put_contents('last_vocs.json', $json_string);
	}

function get_random_voc_and_index($vocs_array, $mode) {
	# avoid asking for a word after it appeared as one of the last <repetition_prevention> words
	do {
		$random_voc_index = rand(0, count($vocs_array)-1);
		} while(is_in_last_vocs($random_voc_index) && count($vocs_array) > 1);
	add_to_last_vocs($random_voc_index);
	return array($random_voc_index, $vocs_array[$random_voc_index]);
	}

function get_bad_voc_and_index($vocs_array, $mode, $forced_limit_score=false) {
	# 33.3% for random pick
	$chance = rand(0, 9);
	if($chance > 6) {
		return get_random_voc_and_index($vocs_array, $mode);
		}

	# set limit score
	if($forced_limit_score === false) {
		$worst_score = 999;
		foreach($vocs_array as $voc) {
			$score = get_voc_score($voc, $mode);
			if($score < $worst_score) $worst_score = $score;
			}
		$limit_score = ($worst_score < 0 ? 0 : $worst_score);
		}
	else {
		$limit_score = $forced_limit_score;
		}

	# choose index of voc to return
	$voc_indexes_to_choose_from = array();
	foreach($vocs_array as $idx => $voc) {
		$score = get_voc_score($voc, $mode);
		if($score <= $limit_score) {
			$voc_indexes_to_choose_from[] = $idx;
			}
		}

	# avoid asking for a word after it appeared as one of the last <repetition_prevention> words
	do {
		if(count($voc_indexes_to_choose_from) > 5) {
			$helper_index = rand(0, count($voc_indexes_to_choose_from)-1);
			$chosen_index = $voc_indexes_to_choose_from[$helper_index];
			}
		else {
			$helper_index = rand(0, count($voc_indexes_to_choose_from)-1);
			return get_bad_voc_and_index($vocs_array, $mode, $limit_score+5);
			}
		} while(is_in_last_vocs($chosen_index));

	add_to_last_vocs($chosen_index);
	return array($chosen_index, $vocs_array[$chosen_index]);
	}

function update_enabled_vocs($vocs_array) {
	$json_string = file_get_contents('voc.json');
	$vocs_obj = json_decode($json_string);

	$vocs_obj->enabled = $vocs_array;
	
	$json_string = json_encode($vocs_obj);
	file_put_contents('voc.json', $json_string);
	}

function get_vocs($state = 'enabled') {
	$json_string = file_get_contents('voc.json');
	$vocs_obj = json_decode($json_string);
	if($state == 'object') return $vocs_obj;
	$vocs_array = $vocs_obj->$state;
	if(count($vocs_array) < 1) return false;
	return $vocs_array;
	}

function get_empty_voc() {
	$voc_obj = new stdClass();
	$voc_obj->jp = '';
	$voc_obj->ger = '';
	$voc_obj->i = '';
	$voc_obj->rf = 0;
	$voc_obj->wf = 0;
	$voc_obj->rt = 0;
	$voc_obj->wt = 0;
	return $voc_obj;
	}

function remove_voc($idx, $state) {
	$json_string = file_get_contents('voc.json');
	$vocs_obj = json_decode($json_string);
	$vocs_array = $vocs_obj->$state;

	array_splice($vocs_array, $idx, 1);
	$vocs_obj->$state = $vocs_array;
	
	$json_string = json_encode($vocs_obj);
	file_put_contents('voc.json', $json_string);
	}

function change_voc_state($idx, $state_from) {
	$json_string = file_get_contents('voc.json');
	$vocs_obj = json_decode($json_string);
	$state_to = ($state_from == 'enabled' ? 'disabled' : 'enabled');
	$vocs_array_from = $vocs_obj->$state_from;
	$vocs_array_to = $vocs_obj->$state_to;

	$tmp_arr = array_splice($vocs_array_from, $idx, 1);
	$changer = $tmp_arr[0];
	$vocs_obj->$state_from = $vocs_array_from;
	$vocs_array_to[] = $changer;
	$vocs_obj->$state_to = $vocs_array_to;
	
	$json_string = json_encode($vocs_obj);
	file_put_contents('voc.json', $json_string);
	}

function cmp_vocs_by_ratio($a, $b) {
	$a = get_voc_ratio($a);
	$b = get_voc_ratio($b);
	if ($a == $b) return 0;
	return (($a > $b) ? -1 : 1);
	}

function kanji_if_kana($voc, $disp_lang, $kanji_id) {
		if($disp_lang == 'jp')
			return '<span title="show kanji" onclick="showById(\''.$kanji_id.'\');">'.$voc->jp.'</span>';
		else
			return $voc->ger;
	}

?>
