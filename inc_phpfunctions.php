<?php

function get_field_value($field_id,$post="",$db="",$date=false){
	
	$ret = "";
	if(isset($post[$field_id])) {
		$ret = $post[$field_id];
	}
	else if(isset($db[$field_id])){
		$ret = $db[$field_id];
	}
	if($date==true) { $ret = date('d-m-Y', strtotime($ret)); }
	return $ret;
	
}

function get_checked_value($field_id,$post="",$db=""){
	$ret = "";
	if(isset($post[$field_id]) and $post[$field_id] == 1) {
		return "checked";
	}
	else if(isset($db[$field_id]) and $db[$field_id] == 1){
		return "checked";
	}

}


function get_selected_value($field_id,$post="",$db="",$sel_value=""){
	$ret = "";
	if(isset($post[$field_id]) and $post[$field_id] == $sel_value) {
		return "selected";
	}
	else if(isset($db[$field_id]) and $db[$field_id] == $sel_value){
		return "selected";
	}

}


?>