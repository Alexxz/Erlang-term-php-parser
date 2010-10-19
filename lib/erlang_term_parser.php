<?php

// just internal config function
function erl_config($param){
    if($param === 'atom_string') return '_abcdefghijklmnopqrstuvwxyz';
    if($param === 'number_string') return '-0123456789.';
    return false;
}

/**
 * internal function parses erlang term from $i position
 * function looks at fist symbol and makes decision what kind of term it must parse
 *
 */
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
    	case $l === '"': // quoted_list
    	    return erl_parse_quoted_list($string, $i);
    	    break;
    	case $l === '{': // tuple
    	    return erl_parse_tuple($string, $i);
    	    break;
    	case $l === '<': // pid
    	    return erl_parse_pid($string, $i);
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
    throw new Exception("Error while parsing term");
}

/**
 * internal funtcion parses "square bracket list" from $i position of string
 */
function erl_parse_list($string, $i){
    $sb_started  = false; // square_bracket
    $list = array();
    $len = strlen($string);
    while($i < $len){
	    $l = $string[$i]; // letter
    	$n = ($i+1) < $len ? $string[$i+1] : false; // next letter
    	switch(true){
    	case $l === '[' && !$sb_started && $n === ']':
    	    $sb_started = true;
    	    break;
    	case $l === '[' && !$sb_started :
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
    throw new Exception("Error while parsing list");
}

/**
 * internal function parses "quoted list from $i position of string"
 */
function erl_parse_quoted_list($string, $i){
    $started  = false;
    $escape = false;
    $list  = array();
    $len   = strlen($string);
    while($i < $len){
	    $l = $string[$i]; // letter
    	switch(true){
    	case $l === '"' && !$started:
    	    $started = true;
    	    break;
    	case $l === "\\" && !$escape:
    	    $escape = true;
    	    break;
    	case $l === '"' && $escape:
	    case $l === "\\" && $escape:
            $list[] = ord($l);
            $escape = false;
            break;
        case $l === '"' && $started:
    	    return array(array('type'=>'list', 'data'=>$list), $i);
            var_dump($list);
    	    break;
        default:
            $list[] = ord($l);
            $escape = false;
    	    break;
    	}
	    $i++;
    }
    throw new Exception("Error while parsing quoted list");
}

/**
 * internal function parses tuple from $i position of string
 */
function erl_parse_tuple($string, $i){
    $started  = false; // 
    $list = array();
    $len = strlen($string);
    while($i < $len){
    	$l = $string[$i];
    	$n = ($i+1) < $len ? $string[$i+1] : false; // next letter
    	switch(true){
       	case $l === '{' && !$started && $n === '}':
    	    $started = true;
    	    break;
        case $l === '{' && !$started:
    	    list($list[], $i) = erl_parse_all($string, $i+1);
    	    $started = true;
    	    continue;
    	case $l === ',':
    	    list($list[], $i) = erl_parse_all($string, $i+1);
            continue;
    	case $l === '}' && $started:
    	    return array(array('type'=>'tuple', 'data'=>$list), $i);
    	    break;
    	default:
    	    throw new Exception("Unexpected symbol $l in $i");
    	    break;
	    }
    	$i++;
    }
    throw new Exception("Error while parsing tuple");
}

/**
 * internal function parses atom from $i position of string
 */
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
    throw new Exception("Error while parsing quoted atom");
}

/**
 * internal function parses int or float from $i position of string
 */
function erl_parse_number($string, $i){
    $substr = substr($string, $i);
    $number_regexp = '/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?/';
    $out = array();
    if(!preg_match_all($number_regexp, substr($string, $i), $out)){
	    throw new Exception("Unexpected sequence in $i");
    }
    $float = (float)$out[0][0];
    $int   = (int)$out[0][0];
    $result = ((float)$int === $float) ? $int : $float;
    return array($result, $i+strlen($out[0][0])-1);
}

/**
 * internal function parses pid from $i position of string
 */
function erl_parse_pid($string, $i){
    $out = array();
    if(!preg_match_all("/^<[0-9]+\.[0-9]+\.[0-9]+>/",substr($string, $i), $out)){
	    throw new Exception("Unexpected sequence in $i");
    }
    return array(array('type'=>'pid', 'data'=>$out[0][0]), $i+strlen($out[0][0])-1);
}


/**
 * public function recurrently goes by resulted term and replaces "stringable" lists to strings
 */
function erl_list2string($node){
    if(!is_array($node)){return $node;}
    if($node['type'] === 'tuple'){
	$new_data = array();
	foreach($node['data'] as $subnode) $new_data[] = erl_list2string($subnode);
	$node['data'] = $new_data;
    }
    if($node['type'] === 'list'){
	$is_string = true;
	$possible_string = '';
	foreach($node['data'] as $subnode){
	    if($is_string = $is_string && is_int($subnode) && $subnode > 0 && $subnode < 256){
	        $possible_string .= chr($subnode);
	    }
	}
	if($is_string && strlen($possible_string) > 0){
	    $node = $possible_string;
	} else {
	    $new_data = array();
	    foreach($node['data'] as $subnode) $new_data[] = erl_list2string($subnode);
	    $node['data'] = $new_data;
	}
    }
    return $node;
}


/**
 * public function to parse string into term
 */
function erl_parse_term($term_string){
    list($result, $offset) = erl_parse_all($term_string, 0);
    return $result;
}
