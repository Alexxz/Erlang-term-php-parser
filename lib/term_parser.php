<?php

function erl_config($param){
    if($param === 'atom_string') return '_abcdefghijklmnopqrstuvwxyz';
    if($param === 'number_string') return '0123456789';
    return false;
}

function _log($string){
    echo microtime(true)." $string\n";
}

function erl_parse_all($string, $i){
    $len = strlen($string);
    while($i < $len){
	$l = $string[$i];
	switch(true){
	case in_array($l, array(' ', "\n")): // stuff
	    break;
	case $l === '[': // list
	    return erl_parse_list($string, $i);
	    break;
	case $l === '{': // term
	    return erl_parse_term($string, $i);
	    break;
	case false !== strpos(erl_config('atom_string'), $l):
	    return erl_parse_atom($string, $i);
	    break;
	case false !== strpos(erl_config('number_string'), $l):
	    return erl_parse_number($string, $i);
	    break;
	default:
	    throw new Exception("Unexpected symbol $l in $i");
	    break;
	}
	$i++;
    }
}

function erl_parse_list($string, $i){
    $sb_started  = false; // square_bracket
    $list = array();
    $len = strlen($string);
    while($i < $len){
	$l = $string[$i];
	switch(true){
	case $l === '[' && !$sb_started && $string[$i+1] === ']':
	    $sb_started = true;
	    break;
	case $l === '[' && !$sb_started:
	    $sb_started = true;
	    list($list[], $i) = erl_parse_all($string, $i+1);
	    continue;
	case $l === ',':
	    list($list[], $i) = erl_parse_all($string, $i+1);
	    continue;
	case $l === ']' && $sb_started:
	    return array(array('type'=>'list', 'data'=>$list), $i);
	    break;
	default:
	    throw new Exception("Unexpected symbol $l in $i");
	    break;
	}
	$i++;
    }
}

function erl_parse_term($string, $i){
    $started  = false; // 
    $list = array();
    $len = strlen($string);
    while($i < $len){
	$l = $string[$i];
	switch(true){
	case $l === '{' && !$started:
	    list($list[], $i) = erl_parse_all($string, $i+1);
	    $started = true;
	    continue;
	case $l === ',':
	    list($list[], $i) = erl_parse_all($string, $i+1);
	    continue;
	case $l === '}' && $started:
	    return array(array('type'=>'term', 'data'=>$list), $i);
	    break;
	default:
	    throw new Exception("Unexpected symbol $l in $i");
	    break;
	}
	$i++;
    }
}

function erl_parse_atom($string, $i){
    $atom = '';
    $len = strlen($string);
    while($i < $len){
	$l = $string[$i];
	switch(true){
	case false !== strpos(erl_config('atom_string'), $l):
	    $atom .= $l;
	    break;
	default:
	    return array(array('type'=>'atom', 'data'=>$atom), $i-1);
	    break;
	}
	$i++;
    }
}


function erl_parse_number($string, $i){
    $number = '';
    $len = strlen($string);
    while($i < $len){
	$l = $string[$i];
	switch(true){
	case false !== strpos(erl_config('number_string'), $l):
	    $number = $number*10 + $l;
	    break;
	default:
	    return array(array('type'=>'number', 'data'=>$number), $i-1);
	    break;
	}
	$i++;
    }
}


function erl_list2string($node){
    if(!is_array($node)){return $node;}
    if($node['type'] === 'term'){
	$new_data = array();
	foreach($node['data'] as $subnode) $new_data[] = erl_list2string($subnode);
	$node['data'] = $new_data;
    }
    if($node['type'] === 'list'){
	$is_string = true;
	$possible_string = '';
	foreach($node['data'] as $subnode){
	    if($is_string = $is_string && $subnode['type'] === 'number' && $subnode['data'] > 0 && $subnode['data'] < 256){
	        $possible_string .= chr($subnode['data']);
	    }
	}
	if($is_string){
	    $node['type'] = 'string';
	    $node['data'] = $possible_string;
	} else {
	    $new_data = array();
	    foreach($node['data'] as $subnode) $new_data[] = erl_list2string($subnode);
	    $node['data'] = $new_data;
	}
    }
    return $node;
}

$term_string = '{[],{},[{}],[1],[a],[1,2],[1,2,3]}';
list($result, $offset) = erl_parse_all($term_string, 0);
print_r(erl_list2string($result));



