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
}

function add_voc($jp, $ger, $i="") {
	$json_string = file_get_contents('voc.json');
	$vocs_obj = json_decode($json_string);

	$new_voc_obj = new stdClass();
	$new_voc_obj->jp = $jp; 	# japanese
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

?>
